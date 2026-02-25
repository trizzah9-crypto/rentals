<?php
require "headd.php";


// Fetch all pending payments
$sql = "SELECT p.id, p.amount, p.payment_month, p.payment_date, p.payment_method, p.reference, 
               t.id AS tenancy_id, tn.name AS tenant_name, h.house_number
        FROM payments p
        INNER JOIN tenancies t ON p.tenancy_id = t.id
        INNER JOIN tenants tn ON t.tenant_id = tn.id
        INNER JOIN houses h ON t.house_id = h.id
        WHERE p.status = 'pending'
        ORDER BY p.payment_date ASC";

$result = $conn->query($sql);

?>



    <div class="container-fluid mt-4">
         <div class="content-body">
          <div class="row page-titles mx-0">
         <div class="container-fluid">
           <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                             <h4 class="card-title">Pending Payments<h4>
                         </div>

                         <div class="card-body">
                            <div class="table-responsive">
                                <table id="example" class="display" style="min-width: 845px">
                                    <thead>
                                        <tr>
                                            <th>Tenant</th>
                                            <th>House</th>
                                            <th>Amount (KES)</th>
                                            <th>Month</th>
                                            <th>Method</th>
                                            <th>Reference</th>
                                            <th>Payment Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody style="color: #333333;">
                                         <?php while($row = $result->fetch_assoc()): ?>

                                       <tr>
                                     <td><?= htmlspecialchars($row['tenant_name']) ?></td>
                                     <td><?= htmlspecialchars($row['house_number']) ?></td>
                                     <td><?= number_format($row['amount'],2) ?></td>
                                     <td><?= $row['payment_month'] ?></td>
                                     <td><?= htmlspecialchars($row['payment_method']) ?></td>
                                     <td><?= htmlspecialchars($row['reference']) ?></td>
                                     <td><?= $row['payment_date'] ?></td>  
                                     
                                

                                                        <td class="table-actions">
                                                            <button class="btn btn-sm btn-success approve-btn" 
                                                                data-id="<?= $row['id'] ?>">Approve</button>
                                                                                                
                                                            <button class="btn btn-sm btn-danger reject-btn" 
                                                                data-id="<?= $row['id'] ?>">Reject</button>
                                                        </td>
                                                </tr>
                                                 <?php endwhile; ?>
                                                 </tbody>
                                    
                                </table>
                            </div>
                        </div>
                    </div>
                 </div>
             </div>  
         </div>
         </div>
         </div>
     </div>
    </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).on('click', '.approve-btn, .reject-btn', function() {
    let paymentId = $(this).data('id');
    let action = $(this).hasClass('approve-btn') ? 'approve' : 'reject';
    let row = $(this).closest('tr');

    $.ajax({
        url: 'ajax/payment_action.php',
        type: 'POST',
        dataType: 'json',
        data: { payment_id: paymentId, action: action },
        success: function(res) {
            if(res.status === 'success') {
                // Remove the row from pending table
                row.fadeOut(300,function(){ $(this).remove(); });

                // Optionally update balance on page
                if(res.rent_balance !== undefined) {
                    $('#tenant-balance').text('KES '+res.rent_balance.toFixed(2));
                }
            } else {
                alert(res.message);
            }
        },
        error: function(xhr){
            console.log(xhr.responseText);
        }
    });
});
</script>

<?php
require "foott.php";
?>
