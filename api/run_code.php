<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$input_data = json_decode(file_get_contents('php://input'), true);
$code = $input_data['code'] ?? '';
$language = strtolower($input_data['language'] ?? 'c');
$custom_input = $input_data['custom_input'] ?? '';

if (empty(trim($code))) {
    echo json_encode(['success' => false, 'error' => 'Code editor is empty.']);
    exit();
}

// Ensure temp directory exists
$temp_dir = __DIR__ . '/../compiler/temp/';
if (!is_dir($temp_dir)) {
    mkdir($temp_dir, 0777, true);
}

$unique_id = uniqid('run_', true);
$output = '';
$error = '';
$execution_time = 0;

$start_time = microtime(true);

switch ($language) {
    case 'c':
    case 'cpp':
        $ext = ($language === 'c') ? 'c' : 'cpp';
        $compiler = ($language === 'c') ? 'gcc' : 'g++';
        $src_file = $temp_dir . $unique_id . '.' . $ext;
        $bin_file = $temp_dir . $unique_id . (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '.exe' : '.out');

        file_put_contents($src_file, $code);

        // Compile
        $compile_cmd = "$compiler \"$src_file\" -o \"$bin_file\" 2>&1";
        $compile_out = shell_exec($compile_cmd);

        if (!file_exists($bin_file)) {
            $error = "Compilation Error:\n" . ($compile_out ?: "Failed to compile.");
        } else {
            // Execute with custom input
            $descriptors = [
                0 => ["pipe", "r"], // stdin
                1 => ["pipe", "w"], // stdout
                2 => ["pipe", "w"]  // stderr
            ];

            $process = proc_open("\"$bin_file\"", $descriptors, $pipes);

            if (is_resource($process)) {
                fwrite($pipes[0], $custom_input);
                fclose($pipes[0]);

                $output = stream_get_contents($pipes[1]);
                fclose($pipes[1]);

                $error = stream_get_contents($pipes[2]);
                fclose($pipes[2]);

                proc_close($process);
            }

            if (file_exists($bin_file)) @unlink($bin_file);
        }
        if (file_exists($src_file)) @unlink($src_file);
        break;

    case 'python':
        $src_file = $temp_dir . $unique_id . '.py';
        file_put_contents($src_file, $code);

        $descriptors = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];

        $process = proc_open("python \"$src_file\" 2>&1", $descriptors, $pipes);

        if (is_resource($process)) {
            fwrite($pipes[0], $custom_input);
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $error = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            proc_close($process);
        }
        if (file_exists($src_file)) @unlink($src_file);
        break;

    default:
        $error = "Unsupported execution language.";
}

$execution_time = round((microtime(true) - $start_time) * 1000, 2);

echo json_encode([
    'success' => empty($error),
    'output' => $output,
    'error' => $error,
    'execution_time' => $execution_time . ' ms'
]);