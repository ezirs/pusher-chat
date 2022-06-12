<?php

    include 'config/config.php';

    require __DIR__ . '/vendor/autoload.php';

    date_default_timezone_set('Asia/Jakarta');

    $options = array(
        'cluster' => '<PUSHER_CLUSTER>',
        'useTLS' => true
    );

    $message = $_POST['message'];
    $username = $_POST['username'];
    $time = date('h:i:sa');
    $username_session = $_POST['username_session'];

?>
<script type="text/javascript">
    if (<?= $username_session ?> != <?= $username ?>) {
        localStorage.setItem("username", <?= $username ?>);
    }
</script>
<?php
    if ($username_session == $username) {
        $status = 'FALSE';
    } else {
        $status = 'TRUE';
    }
    $sql = "insert into tbchat (username, message, time) values ('$username', '$message', '$time')";
    if (mysqli_query($conn, $sql)) {
        $pusher = new Pusher\Pusher(
            '<PUSHER_KEY>',
            '<PUSHER_SECRET>',
            '<PUSHER_APP_ID',
            $options
        );

        $pusher->trigger(
            'ChatMe',
            'my-event',
            array(
                'message' => $message,
                'username' => $username,
                'time' => $time,
                'status' => $status,
                'username_session' => $username_session,
            ),
        );

        echo json_encode(array(
            'message' => $message,
            'username' => $username,
            'time' => $time,
            'status' => $status,
            'username_session' => $username_session,
        ));
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
?>