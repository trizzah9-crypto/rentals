


  <!--**********************************
            Footer start
        ***********************************-->
        <div class="footer">
            <div class="copyright">
                <p>Copyright © Designed &amp; Developed by <a href="#" target="_blank">Quixkit</a> 2019</p>
                <p>Distributed by <a href="https://themewagon.com/" target="_blank">Themewagon</a></p> 
            </div>
        </div>
        <!--**********************************
            Footer end
        ***********************************-->

        <!--**********************************
           Support ticket button start
        ***********************************-->

        <!--**********************************
           Support ticket button end
        ***********************************-->


    </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!--**********************************
        Scripts
    ***********************************-->
    <script src="../assets/js/landlord.js"></script>
    <!-- Required vendors -->
    <script src="../vendor/global/global.min.js"></script>
    <script src="../js/quixnav-init.js"></script>
    <script src="../js/custom.min.js"></script>

    <!-- Vectormap. -->
    <script src="../vendor/raphael/raphael.min.js"></script>
   
    


    <script src="../vendor/circle-progress/circle-progress.min.js"></script>
    <script src="../vendor/chart.js/Chart.bundle.min.js"></script>

    <script src="../vendor/gaugeJS/dist/gauge.min.js"></script>

    <!--  flot-cha.rt js -->
    <script src="../vendor/flot/jquery.flot.js"></script>
    <script src="../vendor/flot/jquery.flot.resize.js"></script>

    <!-- Owl Carou.sel -->
    <script src="../vendor/owl-carousel/js/owl.carousel.min.js"></script>

    <!-- Counter U.p -->
    <script src="../vendor/jqvmap/js/jquery.vmap.min.js"></script>
    <script src="../vendor/jqvmap/js/jquery.vmap.usa.js"></script>
    <script src="../vendor/jquery.counterup/jquery.counterup.min.js"></script>

   


        <script src="../vendor/global/global.min.js"></script>
    <script src="../js/quixnav-init.js"></script>
    <script src="../js/custom.min.js"></script>
    


    <!-- Datatable -->
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../js/plugins-init/datatables.init.js"></script>

  
<script>
function deleteTenancy(id) {
    if (confirm("Are you sure you want to delete this tenancy?")) {
        window.location.href = "delete_tenancy.php?id=" + id;
    }
}
</script>


<script>
$(document).on("click", ".delete-btn", function () {
    let tenancyId = $(this).data("id");
    let row = $(this).closest("tr");

    if (!confirm("Are you sure you want to delete this tenancy?")) {
        return;
    }

    $.ajax({
        url: "ajax/delete_tenancy.php",
        type: "POST",
        data: { id: tenancyId },
        dataType: "json",
        success: function (response) {
            if (response.status === "success") {
                row.fadeOut(500, function () {
                    $(this).remove();
                });
            } else {
                alert(response.message);
            }
        },
        error: function () {
            alert("Something went wrong. Try again.");
        }
    });
});
</script>


</body>

</html>
