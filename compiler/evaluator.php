<?php
require_once __DIR__ . '/../config/database.php';

function evaluateStudentCode($conn, $question_id, $code, $language = 'c') {
    if (empty(trim($code)) || $question_id <= 0) {
        return ['score' => 0.00, 'passed_cases' => 0, 'total_cases' => 0];
    }

    // 1. Fetch Question Max Marks
    $stmt_q = $conn->prepare("SELECT marks FROM questions WHERE id = ? LIMIT 1");
    $stmt_q->bind_param("i", $question_id);
    $stmt_q->execute();
    $res_q = $stmt_q->get_result();
    $q_data = $res_q ? $res_q->fetch_assoc() : null;
    $stmt_q->close();

    $max_marks = (float)($q_data['marks'] ?? 10.0);

    // 2. Fetch All Test Cases for this Question
    $stmt_tc = $conn->prepare("SELECT input, expected_output FROM test_cases WHERE question_id = ?");
    $stmt_tc->bind_param("i", $question_id);
    $stmt_tc->execute();
    $res_tc = $stmt_tc->get_result();
    $test_cases = $res_tc ? $res_tc->fetch_all(MYSQLI_ASSOC) : [];
    $stmt_tc->close();

    $total_cases = count($test_cases);
    if ($total_cases === 0) {
        // If no test cases are defined, give full marks for valid submission
        return ['score' => $max_marks, 'passed_cases' => 0, 'total_cases' => 0];
    }

    // 3. Setup Temp Compiler Directory
    $temp_dir = __DIR__ . '/temp/';
    if (!is_dir($temp_dir)) {
        mkdir($temp_dir, 0777, true);
    }

    $unique_id = uniqid('eval_', true);
    $passed_cases = 0;

    // Normalizing newlines helper
    $normalize = function($str) {
        return trim(preg_replace('/\r\n|\r|\n/', "\n", trim($str)));
    };

    if ($language === 'c' || $language === 'cpp') {
        $ext = ($language === 'c') ? 'c' : 'cpp';
        $compiler = ($language === 'c') ? 'gcc' : 'g++';
        $src_file = $temp_dir . $unique_id . '.' . $ext;
        $bin_file = $temp_dir . $unique_id . (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '.exe' : '.out');

        file_put_contents($src_file, $code);

        // Compile
        $compile_cmd = "$compiler \"$src_file\" -o \"$bin_file\" 2>&1";
        shell_exec($compile_cmd);

        if (file_exists($bin_file)) {
            foreach ($test_cases as $tc) {
                $input = $tc['input'] ?? '';
                $expected = $normalize($tc['expected_output'] ?? '');

                $descriptors = [
                    0 => ["pipe", "r"],
                    1 => ["pipe", "w"],
                    2 => ["pipe", "w"]
                ];

                $process = proc_open("\"$bin_file\"", $descriptors, $pipes);
                if (is_resource($process)) {
                    fwrite($pipes[0], $input);
                    fclose($pipes[0]);

                    $actual_output = stream_get_contents($pipes[1]);
                    fclose($pipes[1]);
                    fclose($pipes[2]);

                    proc_close($process);

                    if ($normalize($actual_output) === $expected) {
                        $passed_cases++;
                    }
                }
            }
            @unlink($bin_file);
        }
        if (file_exists($src_file)) @unlink($src_file);

    } elseif ($language === 'python') {
        $src_file = $temp_dir . $unique_id . '.py';
        file_put_contents($src_file, $code);

        foreach ($test_cases as $tc) {
            $input = $tc['input'] ?? '';
            $expected = $normalize($tc['expected_output'] ?? '');

            $descriptors = [
                0 => ["pipe", "r"],
                1 => ["pipe", "w"],
                2 => ["pipe", "w"]
            ];

            $process = proc_open("python \"$src_file\"", $descriptors, $pipes);
            if (is_resource($process)) {
                fwrite($pipes[0], $input);
                fclose($pipes[0]);

                $actual_output = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                fclose($pipes[2]);

                proc_close($process);

                if ($normalize($actual_output) === $expected) {
                    $passed_cases++;
                }
            }
        }
        if (file_exists($src_file)) @unlink($src_file);
    }

    $calculated_score = ($passed_cases / $total_cases) * $max_marks;

    return [
        'score' => round($calculated_score, 2),
        'passed_cases' => $passed_cases,
        'total_cases' => $total_cases
    ];
}