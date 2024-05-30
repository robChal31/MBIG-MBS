<?php
    ob_start();
   session_start();
   include 'db_con.php';
   if (!isset($_SESSION['username'])){ 
        header("Location: ./main.php");
        exit();
    }

    $action = $_POST['action'];
    $id_master = $_POST['id_master'];
    $namSek=str_replace("'","\'",mysqli_real_escape_string($conn, $_POST['namSek']));
    $namPIC = mysqli_real_escape_string($conn, $_POST['namPIC']);
    $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan']);
    $noHP = mysqli_real_escape_string($conn, $_POST['noHP']);
    $emailPIC = mysqli_real_escape_string($conn, $_POST['emailPIC']);
    $id_ec = mysqli_real_escape_string($conn, $_POST['id_ec']);
    $tgl = mysqli_real_escape_string($conn, $_POST['tgl']);
    $tglExp = mysqli_real_escape_string($conn, $_POST['tglExp']);
    $thn = mysqli_real_escape_string($conn, $_POST['thn']);
    $noSOR = mysqli_real_escape_string($conn, $_POST['noSOR']);
    $statPI = mysqli_real_escape_string($conn, $_POST['statPI']);
    $titleAdopt = isset($_POST['titleAdopt']) ? implode(',', $_POST['titleAdopt']) : '';
    $titleOther = mysqli_real_escape_string($conn, $_POST['titleOther']);
    $keterangan = $_POST['keterangan'];
    
    if(!is_null($titleOther))
    {
        $titleAdopt=$titleAdopt.", ".$titleOther;
    }
    
    $target_dir = "dokumen/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    // Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
      $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
      if($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
      } else {
        //echo "File is not an image.";
        $uploadOk = 0;
      }
    }
    
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" && $imageFileType != "pdf" && $imageFileType != "docx" && $imageFileType != "doc" && $imageFileType != "xls" && $imageFileType != "xlsx" ) {
      //echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
      $uploadOk = 0;
    }
    
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
    // if everything is ok, try to upload file
        if(is_null($action))
        {
          $sql="INSERT INTO `op_masterdata` (`id_master`, `school_name`, `namPIC`, `jabatan`, `noHP`, `emailPIC`, `id_segment`, `id_ec`, `id_sa`, `jenDok`, `fileUrl`, `minQty`, `date`, `expiredDate`, `year`, `nosor`, `statuspi`, `title`, `autoTemplate`, `status`, `statusApprovalDate`,`newMaster`) VALUES (NULL, '$namSek', '$namPIC', '$jabatan', '123123', '$emailPIC', NULL, '$id_ec', NULL, NULL, '$target_file', NULL, '$tgl', '$tglExp', '$thn', '$noSOR', '', '$titleAdopt', '0', NULL, NULL,1)";
        }
        else if ($action=='edit')
        {
            $sql = "UPDATE op_masterdata set school_name='$namSek',id_ec='$id_ec',id_sa='$id_sa',jenDok='$jenDok',date='$tgl',year='$thn',nosor='$noSor',statuspi='$statPI',title='$titleAdopt',status='$status',minQty='$minQty',expiredDate='$tglExp' where id_master='$id_master'";
            
        }
        
        mysqli_query($conn,$sql);
        header('Location: ./masters.php');
        exit();
    
    } else {
      if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        
        if(is_null($action))
        {
          $sql="INSERT INTO `op_masterdata` (`id_master`, `school_name`, `namPIC`, `jabatan`, `noHP`, `emailPIC`, `id_segment`, `id_ec`, `id_sa`, `jenDok`, `fileUrl`, `minQty`, `date`, `expiredDate`, `year`, `nosor`, `statuspi`, `title`, `autoTemplate`, `status`, `statusApprovalDate`,`newMaster`) VALUES (NULL, '$namSek', '$namPIC', '$jabatan', '123123', '$emailPIC', NULL, '$id_ec', NULL, NULL, '$target_file', NULL, '$tgl', '$tglExp', '$thn', '$noSOR', '', '$titleAdopt', '0', NULL, NULL,1)";
        }
        else if ($action=='edit')
        {
            $sql = "UPDATE op_masterdata set school_name='$namSek',id_ec='$id_ec',id_sa='$id_sa',jenDok='$jenDok',date='$tgl',year='$thn',nosor='$noSor',statuspi='$statPI',title='$titleAdopt',status='$status',fileUrl='$target_file',minQty='$minQty' where id_master='$id_master'";

        }
        //echo $sql; die;
        mysqli_query($conn,$sql);
        header('Location: ./masters.php');
        exit();
        
      } else {
        echo "Sorry, there was an error uploading your file.";
      }
    }
    
    
    
    
    
    