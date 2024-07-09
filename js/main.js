$(document).ready(function() {
    $('.delete-lead').click(function() {
        var leadId = $(this).data('lead-id');
        $.post('delete_lead.php', { lead_id: leadId }, function(response) {
            alert(response);
            location.reload();
        });
    });

    $('select').change(function() {
        var leadId = $(this).data('lead-id');
        var newStatus = $(this).val();
        $.post('update_lead.php', { lead_id: leadId, status: newStatus }, function(response) {
            alert(response);
        });
    });

    $('input[type="text"]').change(function() {
        var leadId = $(this).data('lead-id');
        var newFollowUp = $(this).val();
        $.post('update_follow_up.php', { lead_id: leadId, follow_up_details: newFollowUp }, function(response) {
            alert(response);
        });
    });

    $('#syncLeadsBtn').click(function() {
        $.post('check_new_leads.php', function(response) {
            alert(response);
        });
    });
});
