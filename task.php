<?php

$filename = 'tasks.json';

// Buat file jika belum ada
if (!file_exists($filename)) {
    file_put_contents($filename, json_encode([]));
}


// buat function read file
function readTasks($filename)
{
    return json_decode(file_get_contents($filename), true);
}

// buat function print task
function printTasks($tasks)
{
    foreach ($tasks as $task) {
        echo "[{$task['id']}] {$task['title']} - {$task['status']}\n";
    }
}

// buat function save task
function saveTask($filename, $tasks)
{
    file_put_contents($filename, json_encode($tasks, JSON_PRETTY_PRINT));
}


$args = $argv;
array_shift($args); // Hilangkan nama file (task.php)

$command = $args[0] ?? null;
$tasks = readTasks($filename);

switch ($command) {
    case 'add':
        $title = $args[1] ?? null;
        if (!$title) {
            echo "Judul tugas tidak boleh kosong!";
            exit;
        }
        $id = count($tasks) > 0 ? max(array_column($tasks, 'id')) + 1 : 1;
        $tasks[] = ['id' => $id, 'title' => $title, 'status' => 'todo'];
        saveTask($filename, $tasks);
        echo "Tugas berhasil di update!";
        break;

    case 'update':

        $id = $args[1] ?? null;
        $title = $args[2] ?? null;
        foreach ($tasks as &$task) {
            if ($task['id'] == $id) {
                $task['title'] = $title;
                saveTask($filename, $tasks);
                echo "Tugas berhasil di perbarui.\n";
                exit;
            }
        }
        break;

    case 'delete':
        $id = $args[1] ?? null;
        $tasks = array_filter($tasks, fn($t) => $t['id'] != $id);
        saveTask($filename, array_values($tasks));
        echo "Task berhasil dihapus.\n";
        break;

    case 'mark':
        $id = $args[1] ?? null;
        $status = $args[2] ?? null;
        $validStatuses = ['todo', 'in progress', 'done'];
        if (!in_array($status, $validStatuses)) {
            echo "Status tidak valid. Gunakan: todo, in progress, done\n";
            exit;
        }
        foreach ($tasks as &$task) {
            if ($task['id'] == $id) {
                $task['status'] = $status;
                saveTask($filename, $tasks);
                echo "Status task diperbarui.\n";
                exit;
            }
        }
        echo "Tugas tidak ditemukan.\n";
        break;

    case 'list':
        $filter = $args[1] ?? null;
        if (!$filter) {
            printTasks($tasks);
        } else {
            $filtered = array_filter(
                $tasks,
                fn($t) => ($filter === 'progress' && $t['status'] === 'in progress') ||
                    ($filter === 'done' && $t['status'] === 'done') ||
                    ($filter === 'todo' && $t['status'] === 'todo')
            );
            if (count($filtered) > 0) {
                printTasks($filtered);
            } else {
                echo "Belum ada task berstatus done";
            }
        }

        break;
    default:
        echo "Perintah tidak dikenal. Gunakan:\n";
        echo "  php task.php add \"judul\"\n";
        echo "  php task.php update id \"judul\"\n";
        echo "  php task.php delete id\n";
        echo "  php task.php mark id status\n";
        echo "  php task.php list [done|todo|progress]\n";
        break;
}
