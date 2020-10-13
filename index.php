<?php
  if (!empty($_GET['q'])) {
    switch ($_GET['q']) {
      case 'info':
        phpinfo(); 
        exit;
      break;
    }
  }
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Laragon</title>
        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                display: table;
                font-weight: 100;
                font-family: 'Karla';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 96px;
            }

            .opt {
                margin-top: 30px;
            }

            .opt a {
              text-decoration: none;
              font-size: 150%;
            }
            
            a:hover {
              color: red;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title" title="Laragon">Laragon</div>
     
                <div class="info"><br />
				<a href="itemkey.php">Gen ItemKey</a>
				<a href="senditem.php">Send Item</a>
				<a href="signup.php">Reg account</a>
				<a href="chgpass.php">Change pass</a>
				<a href="topupncoin.php">Send Coin</a>
				<a href="bns.php">Hongmoon Store Editor</a>
                </div>
                <div class="opt">
                  <div><a title="Getting Started" href="http://laragon.org/?q=getting-started">Getting Started</a></div>
                </div>
            </div>

        </div>
    </body>
</html>