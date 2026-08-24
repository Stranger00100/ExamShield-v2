case 'python':
case 'py':
    $src_file = $temp_dir . $unique_id . '.py';
    file_put_contents($src_file, $code);

    $descriptors = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ];

    // Using Windows python command launcher
    $process = proc_open("python \"$src_file\"", $descriptors, $pipes);

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