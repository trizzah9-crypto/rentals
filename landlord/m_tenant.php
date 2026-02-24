<?php
require("headd.php");

?>




<div class="content-body">
    <div class="container-fluid">   
        <div class="row">


            <!-- MOVE OUT -->
            <div class="col-xl-6 col-xxl-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Tenant Move-Out</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form id="moveOutForm">
                                <div class="form-group">
                                    <label style="color: #333333;">Select Tenant / House</label>
                                    <select id="select_tenancy" class="form-control" required>
                                        <option value="">Select</option>
                                        <?php
                                        $tenancies = $conn->query("
                                            SELECT t.id, u.name, h.house_number
                                            FROM tenancies t
                                            JOIN tenants te ON t.tenant_id=te.id
                                            JOIN users u ON te.user_id=u.id
                                            JOIN houses h ON t.house_id=h.id
                                            WHERE t.status='active'
                                        ");
                                        while($ten = $tenancies->fetch_assoc()){
                                            echo "<option value='{$ten['id']}'>{$ten['name']} - {$ten['house_number']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label style="color: #333333;">Move-Out Date</label>
                                    <input type="date" id="move_out_date" class="form-control" required>
                                </div>

                                <button type="submit" class="btn btn-danger">Move Out</button>
                            </form>

                            <div id="moveOutMessage" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>

         </div>
    </div>
</div>



<?php
require("foott.php");

?>
