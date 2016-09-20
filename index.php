<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>InfoNET WHOIS servis - pretraga .hr, com.hr i from.hr domena</title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/agency.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Lato:100,300,regular,700,900%7COpen+Sans:300%7CIndie+Flower:regular%7COswald:300,regular,700&subset=latin" rel="stylesheet" type="text/css">
    

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
</head>

<body id="page-top" class="index">
<div class="container-fluid wrapper">
    <div id="top-bar">
    </div>
    <!-- Navigation <nav class="navbar navbar-default navbar-fixed-top"> -->
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header page-scroll">
                
                <!--<a class="navbar-brand page-scroll" href="http:who.infonet.hr">who.infonet.hr</a>-->
                <a id="logo" href="http://who.infonet.hr/">
                    <img src="http://who.infonet.hr/img/whoinfonet-logo.png" alt="Infonet ">
                </a>
            </div>

          
            <div class="col-xs-6 col-md-4 pull-right desc">
                <p class="text-muted">WHOIS pretraga .hr, com.hr, from.hr i iz.hr domena</p>
            </div>  
        </div>
        <!-- /.container-fluid -->
    </nav>

    
    <section id="home">
        <div class="container">
            

      <div class="row">
       
        <div class="col-lg-12 text-center v-center">
          <br><br><br><br><br><br><br>
          <h1>WHOIS pretraga domena</h1>
          <p class="lead text-muted">Pretraga .hr, com.hr, from.hr i iz.hr domena</p>
          
          <br><br><br>
          
          <form class="col-lg-12" id="form1">
            <div class="input-group" style="width:560px;text-align:center;margin:0 auto;">
            <input class="form-control input-xs domain-input" name="domain" title="" placeholder="Unesite naziv domene" type="text">
            <span class="input-group-btn"><input type="submit" name="submit" id="submit" value="Traži!" class="btn btn-info btn-lg search"></span>
              <!--<span class="input-group-btn"><button class="btn btn-lg btn-primary" type="button">Traži!</button></span>-->
            </div>
            <!-- We here at InfoNET don't like crawlers and bots. We don't like to expose your privacy to third party crawlers and bots. -->
            <p class="robotic" id="pot">
              <label>If you're human leave this blank:</label>
              <input name="robotest" type="text" id="robotest" class="robotest" />
            </p>
          </form>
          
        </div>
        
      </div> <!-- /row -->

        </div>
    </section>
    <!-- footer -->
    <footer>
            <div class="container">
                <div class="row">
                    
                    <div class="col-md-4 col-centered">
                        <ul class="list-inline social-buttons">
                            <li>
                                <a target="_blank" class="facebook" href="https://www.facebook.com/pages/InfoNET-hosting/175475552550651">
                                    <i class="fa fa-facebook"></i>
                                </a>
                            </li>
                            <li>
                                <a target="_blank" class="twitter" href="https://twitter.com/infonethr">
                                    <i class="fa fa-twitter"></i>
                                </a>
                            </li>
                        </ul>
                        <span class="copyright">© 2015 InfoNET d.o.o.</span>
                    </div>
                    
                </div>
            </div>
        </footer>
        <!-- end of footer -->
</div>
   <!-- Modal -->
      <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog">
        
          <!-- Modal content-->
          <div class="modal-content">

            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              
            </div>

            <div class="container">
                <div class="modal-body" id="response">
              
                    <?php
                        if(isset($_GET['domain']))
                        {
                            $domain = $_GET['domain'];
                            require_once('check.php');
                            echo '<p id="test">';
                            //echo $domain;
                            echo '</p>';
                           
                        }
                        else 
                        {
                          exit();
                        }


                    ?>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Zatvori</button>
            </div>
          </div>
          
        </div>
      </div>
      <!-- end of modal -->



<script>
    $("#test").ready(function(){
      // we call the function
      $("#myModal").modal();
    });
    

    $('#form1').submit(function(event) {
        event.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'check.php',
            data: $(this).serialize(),
            
            success: function (data) {
               
                //console.log(data);

                $('#response').html(data);
                $("#myModal").modal();
            }
        });
    });
    </script>


</body>


<!-- jQuery -->
    
    
    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
    <script src="js/classie.js"></script>
    <script src="js/cbpAnimatedHeader.js"></script>

    <!-- Contact Form JavaScript -->
    <script src="js/jqBootstrapValidation.js"></script>
    <script src="js/contact_me.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="js/agency.js"></script>

</body>

</html>