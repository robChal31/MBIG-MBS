<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <?php include 'navbar.php'; ?>
            <!-- Navbar End -->
<?php
    $id_kolektif=$_GET['id'];
    $sql = "SELECT * from op_kolektif where id_kolektif='$id_kolektif'";
    $result = mysqli_query($conn, $sql);
    $rowa = mysqli_fetch_assoc($result);
?>            

                <script type='text/javascript'>
                    function addFields(){
                        var subjek = ["English","Maths","Science","Indonesia","Mandarin"];
                        var number = document.getElementById("member").value;
                        var container = document.getElementById("containerss");
                        var submt = document.getElementById("submt");
                        if(number>0)
                        {
                            submt.disabled=false;
                        }
                        while (container.hasChildNodes()) {
                            container.removeChild(container.lastChild);
                        }
                        for (i=0;i<number;i++){

                            container.appendChild(document.createTextNode("Peserta " + (i+1) + " "));
                            var input = document.createElement("input");
                            input.type = "text";
                            input.name = "name[]";
                            input.required=true;
                            container.appendChild(input);
                            
                            var selectList = document.createElement("select");
                            for (var j = 0; j < subjek.length; j++) {
                                var option = document.createElement("option");
                                option.value = subjek[j];
                                option.text = subjek[j];
                                selectList.appendChild(option);
                            }
                            selectList.name= "subjekt[]";
                            container.appendChild(selectList);
                            container.appendChild(document.createElement("br"));
                            container.appendChild(document.createElement("br"));
                        }
                    }
                    
                    //quotafunc
                    
                    function cekquota(selectObject) {
                    var str = selectObject.value;  
                      if (str.length == 0) {
                        document.getElementById("quota").innerHTML = "";
                        return;
                      } else {
                        var xmlhttp = new XMLHttpRequest();
                        xmlhttp.onreadystatechange = function() {
                          if (this.readyState == 4 && this.status == 200) {
                              if(this.responseText!=""){
                                document.getElementById("sisaquota").innerHTML = "Sisa kuotanya adalah " +this.responseText+" pax"; 
                                document.getElementById("member").max = this.responseText;
                              }
                              else
                              {
                                 document.getElementById("sisaquota").innerHTML = "Belum menginput benefit"; 
                              }
                          }
                        };
                        var time_stamp = new Date().getTime();
                        xmlhttp.open("GET", "cekquota.php?type=1&idm=" + str+"&time="+time_stamp, true);
                        xmlhttp.send();
                      }
                    }
                    
                    
                </script>


            <!-- Form Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-light rounded h-100 p-4">
                            <form method="POST" action="benefit-usage-kolektif-edit-action.php" enctype="multipart/form-data">
                            <h6 class="mb-4">School Benefit Usage - Kolektif Input</h6>
                            <div class="form-floating mb-3">
                                <select class="form-select" data-role="select" id="id_master" name="id_master" placeholder="Nama Sekolah"
                                    aria-label="Nama Sekolah">
                                    <option disabled hidden value="">Pilih Sekolah - Tahun</option>
                                    <?php
                                        $sql="select id_master,school_name,year from op_masterdata order by school_name ASC";
                                        if($result = mysqli_query($conn,$sql))
                                        {
                                            while($row = mysqli_fetch_assoc($result))
                                            {
                                                echo "<option value='".$row['id_master']."' ";
                                                if($rowa['id_master']==$row['id_master'])
                                                {
                                                    echo " selected ";
                                                }
                                                echo ">".$row['school_name']." - ".$row['year']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                                <label for="id_sa">Nama Sekolah</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="judul" name="judul" <?php if($id_kolektif>0){echo "value='".$rowa['training_name']."'";} ?>
                                    placeholder="Judul Training" required>
                                <label for="description">Judul Training</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" id="tanggal" name="tanggal" <?php if($id_kolektif>0){echo "value='".$rowa['training_date']."'";} ?>
                                    placeholder="Tanggal Training Training" required>
                                <label for="description">Tanggal Training</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="nama_peserta" name="nama_peserta" <?php if($id_kolektif>0){echo "value='".$rowa['nama_peserta']."'";} ?>
                                    placeholder="Nama Peserta">
                                <label for="description">Nama Peserta</label>
                            </div>
                            <div class="form-floating mb-3" id="containerss"> </div>
                            <input type="hidden" name='id_kolektif' value="<?=$id_kolektif?>">
                            <button type="submit" class="btn btn-lg btn-primary m-2" id="submt">Submit</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Form End -->


<?php include 'footer.php'; ?>