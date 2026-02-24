<?php
require("headd.php")
?>



<div class="content-body">
    <div class="container-fluid">

        <!-- Page title -->
        <div class="row page-titles mx-0">
            <div class="col-sm-6 p-md-0">
                <div class="welcome-text">
                    <h4>Landlord Dashboard</h4>
                    <span class="ml-1">Management</span>
                </div>
            </div>
        </div>

        <!-- MAIN ROW -->
        <div class="row">
   

          <!-- ADD TENANT -->
                        <div class="col-xl-6 col-xxl-12">
                           <div class="card">
                               <div class="card-header">
                                   <h4 class="card-title">Add Tenant</h4>
                               </div>
                               <div class="card-body">
                                   <div class="basic-form">
                                       <form id="addTenantForm">
                                           <div class="form-row">
                                               <div class="form-group col-md-6">
                                                   <label style="color: #333333;">Tenant Name</label>
                                                   <input type="text" id="tenant_name" class="form-control" required>
                                               </div>
                                               <div class="form-group col-md-6">
                                                   <label style="color: #333333;">Email</label>
                                                   <input type="email" id="tenant_email" class="form-control" required>
                                               </div>
                                           </div>

                                           <div class="form-row">
                                               <div class="form-group col-md-6">
                                                   <label style="color: #333333;">Phone</label>
                                                   <input type="text" id="tenant_phone" class="form-control" required>
                                               </div>
                                               <div class="form-group col-md-6">
                                                   <label style="color: #333333;">ID Number</label>
                                                   <input type="text" id="tenant_id_number" class="form-control" required>
                                               </div>
                                           </div>

                                           <div class="form-row">
                                               <div class="form-group col-md-6">
                                                   <label style="color: #333333;">Next of Kin Name</label>
                                                   <input type="text" id="tenant_next_of_kin_name" class="form-control" required>
                                               </div>
                                               <div class="form-group col-md-6">
                                                   <label style="color: #333333;">Next of Kin Phone</label>
                                                   <input type="text" id="tenant_next_of_kin_phone" class="form-control" required>
                                               </div>
                                           </div>

                                           <div class="form-row">
                                               <div class="form-group col-md-12">
                                                   <label style="color: #333333;">Address</label>
                                                   <input type="text" id="tenant_address" class="form-control">
                                               </div>
                                           </div>

                                           <button type="submit" class="btn btn-success">Add Tenant</button>
                                       </form>

                                       <div id="tenantMessage" class="mt-3"></div>
                                   </div>
                               </div>
                           </div>
                        </div>

        </div>
    </div>
</div>



<?php
require("foott.php")
?>