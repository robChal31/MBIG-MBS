 <!-- Footer Start -->
                        <!-- Modal -->
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    Yakin untuk menghapus data ini?
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="deleteBenefitUsage">Delete</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="exampleModal2" tabindex="-1" aria-labelledby="exampleModal2Label" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Update Progress</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <h5>Progress Update</h5>
                    <select name="progress_update" id="progress_update">
                        <option value="0">Belum</option>
                        <option value="1">On Progress</option>
                        <option value="2">Sudah Dijalankan</option>
                    </select><br><br>
                    <h5>CT Note</h5>
                    <textarea id="ct_note" name="ct_note" rows="3" cols="50"></textarea> 
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="updateBenefitUsage">Update</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="exampleModal3" tabindex="-1" aria-labelledby="exampleModal3Label" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModal3Label">Update Keterangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <h5>Keterangan</h5>
                    <textarea id="keteranganInput" name="keteranganInput" rows="3" cols="35"></textarea> 
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="updateKeterangan">Update</button>
                  </div>
                </div>
              </div>
            </div>


            <div class="modal fade" id="exampleModal4" tabindex="-1" aria-labelledby="exampleModal4Label" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModal4Label">Update Tanggal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <h5>Tanggal Pelaksanaan</h5>
                    <input type="date" id="tanggalInput" name="tanggalInput"> 
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="updateTanggal">Update</button>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="modal fade" id="exampleModal5" tabindex="-1" aria-labelledby="exampleModal5Label" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModal5Label">Judul Adopsi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <h5>Progress Update</h5>
                        <select class="form-select" data-role="select" id="titleAdopt" name="titleAdopt[]" placeholder="Judul Adopsi"
                            aria-label="Judul Adopsi" multiple style="height:200px;">
                            <option value="Aim High">Aim High</option>
                            <option value="Big Show">Big Show</option>
                            <option value="Camb Primary Maths">Camb Primary Maths</option>
                            <option value="Camb Primary Science">Camb Primary Science</option>
                            <option value="Cambridge Checkpoint">Cambridge Checkpoint</option>
                            <option value="Chun Hui">Chun Hui</option>
                            <option value="Elevator">Elevator</option>
                            <option value="English Ahead">English Ahead</option>
                            <option value="English Chest">English Chest</option>
                            <option value="English In Mind">English In Mind</option>
                            <option value="Everybody Up">Everybody Up</option>
                            <option value="Family & Friend">Family & Friend</option>
                            <option value="Hang Out">Hang Out</option>
                            <option value="Ibu Pertiwi">Ibu Pertiwi</option>
                            <option value="IGCSE CUP">IGCSE CUP</option>
                            <option value="IGCSE MCE">IGCSE MCE</option>
                            <option value="Juara Matematika">Juara Matematika</option>
                            <option value="Juara Sains">Juara Sains</option>
                            <option value="Maths Ahead">Maths Ahead</option>
                            <option value="MC Maths ">MC Maths </option>
                            <option value="MC Science">MC Science</option>
                            <option value="Meihua ">Meihua </option>
                            <option value="Menjadi Indonesia">Menjadi Indonesia</option>
                            <option value="MPH English">MPH English</option>
                            <option value="MPH Maths ">MPH Maths </option>
                            <option value="MPH Science">MPH Science</option>
                            <option value="My Book">My Book</option>
                            <option value="New Frontiers">New Frontiers</option>
                            <option value="New Maths Champion">New Maths Champion</option>
                            <option value="New Syllabus Mathematics">New Syllabus Mathematics</option>
                            <option value="OWL English">OWL English</option>
                            <option value="OWL Maths">OWL Maths</option>
                            <option value="Prepare">Prepare</option>
                            <option value="Rainbow English">Rainbow English</option>
                            <option value="Rainbow Maths">Rainbow Maths</option>
                            <option value="Rainbow Science">Rainbow Science</option>
                            <option value="Science Ahead">Science Ahead</option>
                            <option value="Sounds Great">Sounds Great</option>
                            <option value="Super Minds">Super Minds</option>
                            <option value="Take Off with English">Take Off with English</option>
                            <option value="Think ">Think </option>
                            <option value="Think Maths">Think Maths</option>
                            <option value="Tracing is Fun">Tracing is Fun</option>
                            <option value="Others">Others</option>
                        </select>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="updateTitleAdopt">Update</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="exampleModal6" tabindex="-1" aria-labelledby="exampleModal6Label" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModal6Label">Generate Voucher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <h5>Pilih Event</h5>
                    <select name="jsonIdEvent" id="jsonIdEvent" onchange="">

                    </select>
                    <div class="ticket-select">
                      <select name="jsonIdTicket" id="jsonIdTicket"><option value="000" disabled>Pilih Ticket</option></select>
                    </div>
                    <div class="input-pax">
                      <select name="input-pax" id="input-pax"></select>
                    </div>

                    <div class="generatedVoucher">

                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="requestVoucher">Generate</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded-top p-4">
                    <div class="row">
                        <div class="col-12 col-sm-6 text-center text-sm-start">
                            &copy; <a href="https://mentarigroups.com">Mentari Group</a>, All Right Reserved. 
                        </div>
                        <div class="col-12 col-sm-6 text-center text-sm-end">
                            <!--/*** This template is free as long as you keep the footer author’s credit link/attribution link/backlink. If you'd like to use the template without the footer author’s credit link/attribution link/backlink, you can purchase the Credit Removal License from "https://htmlcodex.com/credit-removal". Thank you for your support. ***/-->
                            <!--Designed By <a href="https://htmlcodex.com">HTML Codex</a>-->
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer End -->
        </div>
        <!-- Content End -->
 <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://unpkg.com/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script> -->

    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="text/javascript">
      
        $(document).ready(function() {
            $('.toast').toast('show');
            $('[data-bs-toggle="tooltip"]').tooltip();

            $('#collapsibleNav').on('shown.bs.collapse', function () {
                $(this).prev().find('.chevron-icon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
            });

            $('#collapsibleNav').on('hidden.bs.collapse', function () {
                $(this).prev().find('.chevron-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            });

            // Auto collapse the nav if the current URL matches any of the links inside it
            var currentPath = window.location.pathname;
            $('#collapsibleNav a').each(function () {
                if (this.href.includes(currentPath)) {
                    $('#collapsibleNav').collapse('show');
                }
            });

            $('#collapsibleNav2').on('shown.bs.collapse', function () {
                $(this).prev().find('.chevron-icon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
            });

            $('#collapsibleNav2').on('hidden.bs.collapse', function () {
                $(this).prev().find('.chevron-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            });

            $('#collapsibleNav2 a').each(function () {
                if (this.href.includes(currentPath)) {
                    $('#collapsibleNav2').collapse('show');
                }
            });

            function yesnoCheck(that) {
              if (that.value == "Others") {
                  document.getElementById("titleOther").style.display = "block";
              } else {
                  document.getElementById("titleOther").style.display = "none";
              }
            }

            $('#table_id').DataTable({
                dom: 'Bfrtip',
                pageLength: 20,
                order: [
                    [0, 'desc'] 
                ],
                buttons: [
                    { 
                        extend: 'copyHtml5',
                        className: 'btn-custom',
                        attr: {
                            style: 'font-size: .7rem; border: none; font-weight: bold; border-radius: 5px; background-color: blue; color: white;'
                        }
                    },
                    { 
                        extend: 'excelHtml5',
                        className: 'btn-custom',
                        attr: {
                            style: 'font-size: .7rem; border: none; font-weight: bold; border-radius: 5px; background-color: green; color: white;' 
                        }
                    },
                    { 
                        extend: 'csvHtml5',
                        className: 'btn-custom',
                        attr: {
                            style: 'font-size: .7rem; border: none; font-weight: bold; border-radius: 5px; background-color: orange; color: white;'
                        }
                    },
                    { 
                        extend: 'pdfHtml5',
                        className: 'btn-custom',
                        attr: {
                            style: 'font-size: .7rem; border: none; font-weight: bold; border-radius: 5px; background-color: red; color: white;'
                        }
                    }
                ]
            });

            $('[data-toggle="popover"]').popover();

            $('#id_template_benefit').change(function(){
                $('#descript').val($(this).children('option:selected').data('descript'));
                $('#benefit_name').val($(this).children('option:selected').data('benefitname'));
                $('#subbenefit').val($(this).children('option:selected').data('subbenefit'));
                $('#pelaksanaan').val($(this).children('option:selected').data('pelaksanaan'));
                
                var str = $('#id_master').val();
                var idt = $('#id_template_benefit').val();
                    if (str.length == 0) {
                    document.getElementById("quota").innerHTML = "";
                    return;
                    } else {
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            if(this.responseText!=""){
                            document.getElementById("sisaquota").innerHTML = "Sisa kuotanya adalah " +this.responseText+" pax"; 
                            $('#submt').show(200); 
                            $('#submt').prop("disabled", false);
                            document.getElementById("member").max = this.responseText;
                            }
                            else
                            {
                                document.getElementById("sisaquota").innerHTML = "Belum menginput benefit";
                                $('#submt').hide(200); 
                            }
                        }
                    };
                    var time_stamp = new Date().getTime();
                    xmlhttp.open("GET", "newcekquota.php?idb="+idt+"&idm=" + str+"&time="+time_stamp, true);
                    xmlhttp.send();
                    }
            });

        } );
        
        var exampleModal = document.getElementById('exampleModal')
        exampleModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            var rowid = button.getAttribute('data-bs-rowid')
            var benefittype = button.getAttribute('data-bs-benefittype')
            var modalTitle = exampleModal.querySelector('.modal-title')
            modalTitle.textContent = 'Delete data? ID = ' + rowid
            document.getElementById('deleteBenefitUsage').setAttribute('onclick','deleteData('+benefittype+','+rowid+')');
        })

        var exampleModal2 = document.getElementById('exampleModal2')
        exampleModal2.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            var rowid = button.getAttribute('data-bs-rowid')
            var benefittype = button.getAttribute('data-bs-benefittype')
            var progress_current = button.getAttribute('data-bs-progressupdate')
            var modalTitle = exampleModal.querySelector('.modal-title')
            modalTitle.textContent = 'Update progress? ID = ' + rowid
            document.getElementById('updateBenefitUsage').setAttribute('onclick','updateProgressData('+benefittype+','+rowid+')');
        })


        var exampleModal3 = document.getElementById('exampleModal3')
        exampleModal3.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            var rowid = button.getAttribute('data-bs-rowid')
            var keterangan = button.getAttribute('data-bs-keterangan')
            var modalTitle = exampleModal3.querySelector('.modal-title')
            modalTitle.textContent = 'Update keterangan? ID = ' + rowid;
            $('#keteranganInput').val(keterangan);
            document.getElementById('updateKeterangan').setAttribute('onclick','updateKeteranganData('+rowid+')');
        })

        var exampleModal3 = document.getElementById('exampleModal4')
        exampleModal3.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            var rowid = button.getAttribute('data-bs-rowid')
            var tanggal = button.getAttribute('data-bs-tanggal')
            var modalTitle = exampleModal3.querySelector('.modal-title')
            modalTitle.textContent = 'Update tanggal? ID = ' + rowid;
            $('#tanggalInput').val(tanggal);
            document.getElementById('updateTanggal').setAttribute('onclick','updateTanggalData('+rowid+')');
        })

        var exampleModal5 = document.getElementById('exampleModal5')
        exampleModal5.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            var rowid = button.getAttribute('data-bs-rowid')
            var modalTitle = exampleModal5.querySelector('.modal-title')
            modalTitle.textContent = 'Update title? ID = ' + rowid
            document.getElementById('updateTitleAdopt').setAttribute('onclick','updateTitleAdopt('+rowid+')');
        })

        var exampleModal6 = document.getElementById('exampleModal6')
        exampleModal6.addEventListener('show.bs.modal', function (event) {
          const apiUrl = 'https://hadiryuk.id/api/event';
          const apiUrl2 = 'https://hadiryuk.id/api/ticket/';
          var button = event.relatedTarget
          var qty = button.getAttribute('data-bs-qty'); //sisaqty
          const jsonDataDropdown = document.getElementById('jsonIdEvent');
          const jsonDataDropdown2 = document.getElementById('jsonIdTicket');
          const jsonDataDropdown3 = document.getElementById('input-pax');
          fetch(apiUrl)
            .then(response => {
                // Check if the request was successful (status code 200)
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                
                // Parse the JSON data from the response
                return response.json();
            })
            .then(data => {
                // Handle the parsed JSON data
                const option = document.createElement('option');
                option.value="0";
                option.text="Pilih Event";
                option.disabled="disabled";
                option.selected="selected";
                jsonDataDropdown.appendChild(option);
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id_event;  // Replace 'value' with the actual property name in your JSON
                    option.text = item.title+" | "+item.date_start;    // Replace 'text' with the actual property name in your JSON
                    jsonDataDropdown.appendChild(option);
                });
            })
            .catch(error => {
                // Handle errors during the fetch operation
                console.error('Error fetching JSON:', error);
            });


            function updateSecondDropdown(selectedValue) {
                // Fetch JSON data from the specified URL based on the selected value
                var apiUrl = `${apiUrl2}${selectedValue}`;

                fetch(apiUrl)
                    .then(response => {
                        // Check if the request was successful (status code 200)
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }

                        // Parse the JSON data from the response
                        return response.json();
                    })
                    .then(data => {
                        // Clear existing options in the second dropdown
                        jsonDataDropdown2.innerHTML = '';
                        const option = document.createElement('option');
                        option.value="0";
                        option.text="Pilih Ticket";
                        option.disabled="disabled";
                        option.selected="selected";
                        jsonDataDropdown2.appendChild(option);
                        // Loop through the data and create an option element for each item
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.id_ticket;  // Replace 'value' with the actual property name in your JSON
                            option.text = item.ticket_name+" - "+item.price;    // Replace 'text' with the actual property name in your JSON
                            jsonDataDropdown2.appendChild(option);
                        });

                        // Optional: You can set a default selected option if needed
                        // secondDropdown.value = 'default_value';
                    })
                    .catch(error => {
                        // Handle errors during the fetch operation
                        console.error('Error fetching JSON:', error);
                    });
            }

            function updateThirdDropdown(selectedValue) {
                // Fetch JSON data from the specified URL based on the selected value
                jsonDataDropdown3.innerHTML = '';
                const option = document.createElement('option');
                        option.value="0";
                        option.text="Input Pax";
                        option.disabled="disabled";
                        option.selected="selected";
                        jsonDataDropdown3.appendChild(option);

                for(a=1;a<=qty;a++)
                {
                    const option = document.createElement('option');
                    option.value = a;  // Replace 'value' with the actual property name in your JSON
                    option.text = a+" pax";    // Replace 'text' with the actual property name in your JSON
                    jsonDataDropdown3.appendChild(option);
                }
            }

            jsonDataDropdown.addEventListener('change', function () {
                idevent = this.value;
                updateSecondDropdown(this.value);
            });

            jsonDataDropdown2.addEventListener('change', function () {
                idticket = this.value;
                updateThirdDropdown(this.value);
            });

            
            var rowid = button.getAttribute('data-bs-rowid')
            var modalTitle = exampleModal6.querySelector('.modal-title')
            modalTitle.textContent = 'Generate Voucher id:' + rowid

            document.getElementById('requestVoucher').setAttribute('onclick','requestVoucher('+rowid+')');
        })

        function deleteData(benefittype,rowid){
            $.ajax({
                url: 'new-benefit-delete.php',
                type: 'post',
                data: {id_benefit:rowid,id_template_benefit:benefittype},
                success: function(response){
                    if(response=='')
                    {
                        location.reload();
                    }
                    else
                    {
                        console.log(response);
                    }
                }
            });
        }

        function updateProgressData(benefittype,rowid){
            var progress_update  = document.getElementById('progress_update').value
            var ct_note = document.getElementById('ct_note').value
            console.log(benefittype+" "+rowid+" "+progress_update+" "+ct_note);
            $.ajax({
                url: 'benefit-usage-progress-update.php',
                type: 'post',
                data: {id_benefit:rowid,progress_update:progress_update,ct_note:ct_note},
                success: function(response){
                    if(response=='')
                    {
                        //console.log(response);
                        location.reload();
                    }
                    else
                    {
                        console.log(response);
                    }
                }
            });
        }

        function updateKeteranganData(rowid){
            var keterangan = document.getElementById('keteranganInput').value
            console.log(rowid+" "+keterangan);
            $.ajax({
                url: 'new-benefit-keterangan-update.php',
                type: 'post',
                data: {id_benefit:rowid,keterangan:keterangan},
                success: function(response){
                    if(response=='')
                    {
                        //console.log(response);
                        location.reload();
                    }
                    else
                    {
                        console.log(response);
                    }
                }
            });
        }

        function updateTanggalData(rowid){
            var tanggal = document.getElementById('tanggalInput').value
            console.log(rowid+" "+tanggal);
            $.ajax({
                url: 'new-benefit-tanggal-update.php',
                type: 'post',
                data: {id_benefit:rowid,tanggal:tanggal},
                success: function(response){
                    if(response=='')
                    {
                        //console.log(response);
                        location.reload();
                    }
                    else
                    {
                        console.log(response);
                    }
                }
            });
        }
        
        function updateTitleAdopt(rowid){
            var titleAdopt  = $('#titleAdopt').serializeArray();
            titleAdopt = JSON.stringify(titleAdopt);
            console.log(+rowid+" "+titleAdopt);
            $.ajax({
                url: 'masters-title-update.php',
                type: 'post',
                data: {id_master:rowid,titleAdopt:titleAdopt},
                success: function(response){
                    if(response=='')
                    {
                        //console.log(response);
                        location.reload();
                    }
                    else
                    {
                        console.log(response);
                    }
                }
            });
        }
        
        function requestVoucher(rowid){
          const phpUrl = 'request-voucher.php';

          // Data to be sent in the POST request
          const postData = {
              rowid: rowid,
              idevent: $('#jsonIdEvent').val(),
              idticket: $('#jsonIdTicket').val(),
              quota: $('#input-pax').val()
          };

          // Make the POST request using jQuery
          $.ajax({
              url: phpUrl,
              method: 'POST',
              contentType: 'application/json',
              data: JSON.stringify(postData),
              success: function(data) {
                  // Handle the response data, if needed
                  console.log('Response from PHP:', data);
              },
              error: function(xhr, status, error) {
                  // Handle errors during the request
                  console.error('Error:', error);
              }
          });
          
        }
        
    </script>
    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>