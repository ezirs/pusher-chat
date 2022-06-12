<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pusher APP</title>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">

    <script type="text/javascript">
        var username = localStorage.getItem("username");
        var setUsername = "?username=" + localStorage.getItem('username');
        if (username == null) {
            username = prompt("Please enter your username:", "");
            if (username != "" && username != null) {
                localStorage.setItem("username", username);
                window.location = setUsername;
            }
        }

        function GetURLParameter(sParam) {
            var sPageURL = window.location.search.substring(1);
            var sURLVariables = sPageURL.split('&');
            for (var i = 0; i < sURLVariables.length; i++) {
                var sParameterName = sURLVariables[i].split('=');
                if (sParameterName[0] == sParam) {
                    return sParameterName[1];
                }
            }
        }

        var parameter = GetURLParameter('username');

        if (parameter == null) {
            window.location = setUsername;
        } else if (parameter != username) {
            window.location = setUsername;
        }
    </script>
</head>
<body>

<?php
    if ($_GET['username']) {
?>

    <div class="modal fade show" id="chat" tabindex="-1" aria-labelledby="chat" aria-hidden="true" role="dialog" style="display: block;">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title w-100" id="chat">
                        <div class="input-group mb-2">
                            <span class="input-group-text" id="username">@</span>
                            <input type="text" class="form-control" id="userchat" placeholder="Username">
                        </div>
                    </div>
                </div>
                <div class="modal-body" id="messages" style="height: 1000px;">
                    <?php
                    include 'config/config.php';
                    $query = "select * from tbchat";
                    $result = $conn->query($query);
                        while ($row = mysqli_fetch_array($result)) {
                            if ($row['username'] == $_GET['username']) {
                                echo '<div class="list-group-item list-group-item-action mb-1 active" aria-current="true">';
                                echo '<div class="d-flex w-100 justify-content-between">';
                                echo '<h5 class="mb-1">'. $row['username'] .'</h5>';
                                echo '<small>'. $row['time'] .'</small>';
                                echo '</div>';
                                echo '<p class="mb-1">'. $row['message'] .'</p>';
                                echo '</div>';
                            } else {
                                echo '<div class="list-group-item list-group-item-action mb-1" aria-current="true">';
                                echo '<div class="d-flex w-100 justify-content-between">';
                                echo '<h5 class="mb-1">'. $row['username'] .'</h5>';
                                echo '<small>'. $row['time'] .'</small>';
                                echo '</div>';
                                echo '<p class="mb-1">'. $row['message'] .'</p>';
                                echo '</div>';
                            }
                            
                        }
                    ?>
                </div>
                <div class="modal-footer" style="z-index: 999;">
                    <form class="w-100">
                        <div class="input-group">
                            <input type="text" class="form-control" id="message" placeholder="Write your message!" autocomplete="off">
                            <button type="submit" class="btn btn-outline-success"><i class="bi bi-send"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php
    } else {
?>
    <script type="text/javascript">
        alert('ERROR');
    </script>
<?php
    }
?>

<script type="text/javascript">
    document.getElementById('userchat').value = username;
    var el = document.getElementById('messages');
    el.scrollTop = el.scrollHeight;

    function checkChangeUsername(username_session, status) {
        var username_now = "<?= $_GET['username'] ?>";
        if (status == "TRUE" && username_now == username_session) {
            alert('Your username has changed, the page will refresh automatically.');
            window.location = setUsername;
        }
    }

    var pusher = new Pusher('<PUSHER_KEY>', {
        cluster: '<PUSHER_CLUSTER>'
    });

    var channel = pusher.subscribe('ChatMe');
    var userchat = document.getElementById('userchat').value;
    channel.bind('my-event', function(data) {
        if (data.username == userchat) {
            $('#messages').append(`
                <div class="list-group-item list-group-item-action mb-1 active" aria-current="true">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">`+ data.username +`</h5>
                        <small>`+ data.time +`</small>
                    </div>
                    <p class="mb-1">`+ data.message +`</p>
                </div>
            `);
        } else {
            $('#messages').append(`
                <div class="list-group-item list-group-item-action mb-1" aria-current="true">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">`+ data.username +`</h5>
                        <small>`+ data.time +`</small>
                    </div>
                    <p class="mb-1">`+ data.message +`</p>
                </div>
            `);
        }
        checkChangeUsername(data.username_session, data.status);
        el.scrollTop = el.scrollHeight;
    });

    $(function() {
        $('form').submit(function() {
            var userchat = document.getElementById('userchat').value;
            var message = document.getElementById('message').value;
            if (userchat == '') {
                alert('Username cannot be empty!');
                return false;
            } else if (message == '') {
                alert('Message cannot be empty!');
                return false;
            } else {
                localStorage.setItem("username", userchat);
                $.post('ajax.php', {
                    message : $('#message').val(),
                    username : userchat,
                    username_session : "<?= $_GET['username'] ?>",
                });
                $('#message').val('')
                return false;
            }
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
</body>
</html>
