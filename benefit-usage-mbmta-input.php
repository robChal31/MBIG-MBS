<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <?php include 'navbar.php'; ?>
            <!-- Navbar End -->
  <?php
    $id_mbmta=$_GET['id'];
    $sql = "SELECT * from op_mbmta where id_mbmta='$id_mbmta'";
    $result = mysqli_query($conn, $sql);
    $rowa = mysqli_fetch_assoc($result);
?>               

                <script type='text/javascript'>
                    function addFields(){
                        var submt = document.getElementById("submt");
                        var number = document.getElementById("member").value;
                        var container = document.getElementById("containerss");
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
                            
                            container.appendChild(document.createElement("br"));
                            container.appendChild(document.createElement("br"));
                        }
                        if(number>0)
                        {
                            submt.disabled=false;
                        }
                    }
                    
                    
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
                        xmlhttp.open("GET", "cekquota.php?type=3&idm=" + str+"&time="+time_stamp, true);
                        xmlhttp.send();
                      }
                    }

                    function hitungJumlah()
                    {
                        var submt = document.getElementById("submt");
                        var q1 = +document.getElementById("member1").value;
                        var q2 = +document.getElementById("member2").value;
                        var q3 = +document.getElementById("member3").value;
                        var qj = document.getElementById("member");
                        var qlimit = +document.getElementById("member").max
                        if(q1+q2+q3 <= qlimit)
                        {
                            qj.value=q1+q2+q3;
                            submt.disabled=false;
                        } 
                        else
                        {
                            qj.value=q1+q2+q3;
                            submt.disabled=true;
                        }
                    }
                </script>



            <!-- Form Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-light rounded h-100 p-4">
                            <form method="POST" action="benefit-usage-mbmta-input-action.php" enctype="multipart/form-data">
                            <h6 class="mb-4">School Benefit Usage - MBMTA</h6>
                            <div class="form-floating mb-3">
                                <select class="form-select" data-role="select" id="id_master" name="id_master" placeholder="Nama Sekolah"
                                    aria-label="Nama Sekolah" onchange="cekquota(this)">
                                    <option disabled hidden value="">Pilih Sekolah - Tahun</option>
                                    <?php
                                        $sql="select b.id_master,b.school_name,b.year from op_benefit a left join op_masterdata b on a.id_master=b.id_master where a.approval=1 and a.id_benefittype=3 order by school_name ASC";
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
                                <div id="kuota">
                                    
                                </div>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="number" class="form-control" id="member1" name="member1" onchange="hitungJumlah()" <?php if($id_mbmta>0){ echo "value='".$rowa['mbmta1']."'";}?>
                                    placeholder="MBMTA 1">
                                <label for="description">MBMTA 1</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="number" class="form-control" id="member2" name="member2" onchange="hitungJumlah()" <?php if($id_mbmta>0){ echo "value='".$rowa['mbmta2']."'";}?>
                                    placeholder="MBMTA 2">
                                <label for="description">MBMTA 2</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="number" class="form-control" id="member3" name="member3" onchange="hitungJumlah()" <?php if($id_mbmta>0){ echo "value='".$rowa['mbmta3']."'";}?>
                                    placeholder="Jumlah total peserta">
                                <label for="description">MBMTA 3</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="number" class="form-control" id="member" name="member" <?php if($id_mbmta>0){ echo "value='".$rowa['jumlah_peserta']."'";}?>
                                    placeholder="Jumlah total peserta"><div id="sisaquota"></div>
                                <label for="description">Jumlah total peserta</label>
                            </div>
                            <div class="form-floating mb-3" id="containerss"> </div>
                            <?php
                                if($id_mbmta>0)
                                {
                                    echo "<input type='hidden' name='action' value='edit'>";
                                    echo "<input type='hidden' name='id_mbmta' value='".$id_mbmta."'>";
                                }
                            ?>
                            <button type="submit" class="btn btn-lg btn-primary m-2" id="submt" disabled>Submit</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Form End -->


<?php include 'footer.php'; ?>