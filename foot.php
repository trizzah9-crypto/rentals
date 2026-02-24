

        <!--**********************************
            Footer start
        ***********************************-->
        <div class="footer">
            <div class="copyright">
                <p>Copyright © Designed &amp; Developed by <a href="#" target="_blank">Quixkit</a> 2019</p>
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
    <!-- Required vendors -->
    <script src="./vendor/global/global.min.js"></script>
    <script src="./js/quixnav-init.js"></script>
    <script src="./js/custom.min.js"></script>
    
<script>
let idleTime = 0;
const idleLimit = 15 * 60; // 15 minutes

setInterval(() => {
    idleTime++;
    if (idleTime >= idleLimit) {
        window.location.href = "/logout.php";
    }
}, 1000);

['mousemove','keypress','click','scroll'].forEach(event => {
    document.addEventListener(event, () => idleTime = 0);
});
</script>


</body>

</html>