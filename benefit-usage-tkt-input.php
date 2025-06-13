<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <?php include 'navbar.php'; ?>
            <!-- Navbar End -->
            

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
                        xmlhttp.open("GET", "cekquota.php?type=15&idm=" + str+"&time="+time_stamp, true);
                        xmlhttp.send();
                      }
                    }
                </script>



            <!-- Form Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-light rounded h-100 p-4">
                            <form method="POST" action="benefit-usage-s2-input-action.php" enctype="multipart/form-data">
                            <h6 class="mb-4">School Benefit Usage - S2</h6>
                            <div class="form-floating mb-3">
                                <select class="form-select" data-role="select" id="id_master" name="id_master" placeholder="Nama Sekolah"
                                    aria-label="Nama Sekolah" onchange="cekquota(this)">
                                    <option disabled hidden value="">Pilih Sekolah - Tahun</option>
                                    <?php
                                        $sql="select b.id_master,b.school_name,b.year from op_benefit a left join op_masterdata b on a.id_master=b.id_master where a.approval=1 and a.id_benefittype=15 order by school_name ASC";
                                        if($result = mysqli_query($conn,$sql))
                                        {
                                            while($row = mysqli_fetch_assoc($result))
                                            {
                                                echo "<option value='".$row['id_master']."'>".$row['school_name']." - ".$row['year']."</option>";
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
                                <input type="date" class="form-control" id="tanggal" name="tanggal"
                                    placeholder="Tanggal Training Training" >
                                <label for="description">Tanggal Training</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="description" name="description"
                                    placeholder="Description" required>
                                <label for="description">Description s2</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input type="number" class="form-control" id="member" name="member"
                                    placeholder="Jumlah total peserta"><div id="sisaquota"></div><a href="#" id="filldetails" onclick="addFields()">Isi Data</a>
                                <label for="description">Jumlah total peserta</label>
                            </div>
                            <div class="form-floating mb-3" id="containerss"> </div>
                            
                            <button type="submit" class="btn btn-lg btn-primary m-2" disabled id="submt">Submit</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Form End -->


<?php include 'footer.php'; ?>