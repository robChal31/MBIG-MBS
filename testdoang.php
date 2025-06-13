<html>
<div class="card">
            <div class="content mb-0 mt-2">
                <form action="https://mentarigroups.com/vmc/app_api/send_recognition.php" method="post" target="_blank" class="formApresiasi">
                    <fieldset>
                        <div class="form-field form-name">
                            <label class="color-theme" for="receiver">Penerima:<span>(required)</span></label>
                            <select id="receiver" name="receiver" data-live-search="true" >
                               <option value="239">mike</option>
                            </select>
                        </div>
                        <div class="cont-main mt-2" id="daftar-badge" >
                       
                        </div>
                        <div class="form-field form-text">
                            <label class="contactMessageTextarea color-theme" for="contactMessageTextarea">Pesan:<span>(required)</span></label>
                            <textarea name="pesan" class="round-small" id="contactMessageTextarea" style="height:80px;" required>Bagus ini hanya test</textarea>
                        </div>
                        <div class="form-button">
                            <input type="submit" class="btn bg-highlight text-uppercase font-900 btn-m btn-full rounded-sm  shadow-xl contactSubmitButton" id="kirimApresiasiBut" value="Send Message"/>
                            <input type="hidden" name="email" value="robanichalif.aa@gmail.comss">
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
</html>
<script>
            document
          .getElementById('kirimApresiasiBut')
          .addEventListener('click', function (e) {
            e.preventDefault(); // Prevent the default form submission

            // Create a FormData object from the form
            var form = document.querySelector('.formApresiasi');
            var formData = new FormData(form);

            // Convert FormData to URL-encoded string
            var urlEncodedData = new URLSearchParams(formData).toString();

            // Create an XMLHttpRequest object
            var xhr = new XMLHttpRequest();
            xhr.open(
              'POST',
              'https://mentarigroups.com/vmc/app_api/send_recognition2.php',
              true
            );
            xhr.setRequestHeader(
              'Content-Type',
              'application/x-www-form-urlencoded'
            );

            // Define what happens on successful data submission
            xhr.onload = function () {
              if (xhr.status >= 200 && xhr.status < 400) {
                var response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                  document
                    .querySelector('.form-sent')
                    .classList.remove('disabled');
                  setTimeout(() => {
                    updateApresiasiCount();
                  }, 2000);

                  setTimeout(() => {
                    form.reset();
                  }, 3000);

                  setTimeout(() => {
                    document
                      .querySelector('.form-sent')
                      .classList.add('disabled');
                  }, 2000);
                } else {
                  // Add error handling logic here
                  alert(response.message);
                }
              } else {
                console.error('Error:', xhr.statusText);
                alert('An error occurred. Please try again.');
              }
            };

            // Define what happens in case of an error
            xhr.onerror = function () {
              console.error('Request failed');
              alert('An error occurred. Please try again.');
            };

            // Send the request with the form data
            xhr.send(urlEncodedData);
          });
</script>