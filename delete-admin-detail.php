<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
include_once ('sesion.php');
if ($_SESSION['autentificado'] == false){
    header('location:logout.php');
        
    }

    $adminid=$_SESSION['adminid'];
    $rat=mysqli_query($con,"select Rango from tbladmin where ID='$adminid'");
    $row=mysqli_fetch_array($rat);
    $rank2=$row['AdminName'];
    if($row['Rango']==""){
        echo '<script>alert("Acceso Denegado!")</script>';
        header('location:dashboard.php');
    }

   if(isset($_POST['submit']))
  {
    
    $cid=$_GET['upid'];

    
   $query=mysqli_query($con, "delete from tbladmin where ID='$cid'");
    if ($query) {
echo '<script>alert("Dato exitosamente borrado!")</script>';
echo "<script>window.location.href ='view-adminss.php'</script>";
  }
  else
    {
      $msg="Algo salio mal. Por favor intentalo otra vez.";
    }

  
} 

?>

<!doctype html>
<html class="no-js" lang="en">

<head>
<link rel="shortcut icon" type="image/x-icon" href="imagenes/iconito.ico" />
    <title>Eliminacion de Encargados</title>
    

    <link rel="apple-touch-icon" href="apple-icon.png">
  

    <link rel="stylesheet" href="vendors/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="vendors/themify-icons/css/themify-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/selectFX/css/cs-skin-elastic.css">
    <link rel="shortcut icon" type="image/x-icon" href="imagenes/iconito.ico" />

    <link rel="stylesheet" href="assets/css/style.css">

    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800' rel='stylesheet' type='text/css'>



</head>

<body>
    <!-- Left Panel -->

    <?php include_once('includes/sidebar.php');?>

    <div id="right-panel" class="right-panel">

        <!-- Header-->
        <?php include_once('includes/header.php');?>

        <div class="breadcrumbs">
            <div class="col-sm-4">
                <div class="page-header float-left">
                    <div class="page-title">
                        <h1>Eliminacion de Encargados</h1>
                    </div>
                </div>
            </div>
            <div class="col-sm-8">
                <div class="page-header float-right">
                    <div class="page-title">
                        <ol class="breadcrumb text-right">
                        <li><a href="dashboard.php">Menu</a></li>
                            <li><a href="view-admins.php">Encargados</a></li>
                            <li class="active">Eliminacion de Encargados</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content mt-3">
            <div class="animated fadeIn">


                <div class="row">
                    <div class="col-lg-6">
                       <!-- .card -->

                    </div>
                    <!--/.col-->

                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header"><strong>Manejo de Admins</strong><small> </small></div>
                           
                                <p style="font-size:16px; color:red" align="center"> <?php if($msg){
    echo $msg;
  }  ?> </p>
                            <div class="card-body card-block">
 <?php
 $cid=$_GET['upid'];
$ret=mysqli_query($con,"select * from tbladmin where id='$cid'");
$cnt=1;
while ($row=mysqli_fetch_array($ret)) {

?>                       <table border="1" class="table table-bordered mg-b-0">
   
                       
<tr>
                                <th>Nombre</th>
                                   <td><?php  echo $row['AdminName'];?></td>
                                   </tr>       
                                <tr>
                                <th>Telefono</th>
                                   <td><?php  echo $row['MobileNumber'];?></td>
                                   </tr>
                                   <tr>
                                    <th>Correo</th>
                                      <td><?php  echo $row['Email'];?></td>
                                  </tr>
                                  <tr>
                                <th>Usuario</th>
                                   <td><?php  echo $row['UserName'];?></td>
                                   </tr> 
                                      <tr>  
                                       <th>Contraseña</th>
                                        <td><?php  echo $row['Password'];?></td>
                                    </tr>
                                    <tr>  
                                       <th>Fecha de Registro</th>
                                        <td><?php  echo $row['AdminRegdate'];?></td>
                                    </tr>
                                    <tr>  
                                       <th>Ultimo Ingreso</th>
                                        <td><?php  echo $row['LastLogin'];?></td>
                                    </tr>
      




</table>
                                                    </div>
                                                    
                                                    
                                                    
                                                    
                                                </div>
                                                </table>
<table class="table mb-0">




  <form name="submit" method="post" enctype="multipart/form-data"> 

  <tr>
    <th>Esta Seguro/a?</th>
  </tr>
<tr>


  <tr align="center">
    <td colspan="2"><button type="submit" name="submit" class="btn btn-primary btn-sm"><i class="fa fa-dot-circle-o"></i> Listo</button></td>
  </tr>
  </form>
     

<?php  ?>
</table>


  

  

<?php } ?>

                                            </div>



                                           
                                            </div>
                                        </div><!-- .animated -->
                                    </div><!-- .content -->
                                </div><!-- /#right-panel -->
                                <!-- Right Panel -->


                            <script src="vendors/jquery/dist/jquery.min.js"></script>
                            <script src="vendors/popper.js/dist/umd/popper.min.js"></script>

                            <script src="vendors/jquery-validation/dist/jquery.validate.min.js"></script>
                            <script src="vendors/jquery-validation-unobtrusive/dist/jquery.validate.unobtrusive.min.js"></script>

                            <script src="vendors/bootstrap/dist/js/bootstrap.min.js"></script>
                            <script src="assets/js/main.js"></script>
</body>
</html>
<?php   ?>
