<?php

return [
    // Success notifications
    'ticket_created' => 'Ticket created successfully.',
    'ticket_updated' => 'Ticket updated successfully.',
    'ticket_deleted' => 'Ticket deleted successfully.',
    'ticket_pending' => 'Ticket changed to pending.',
    'ticket_resumed' => 'Ticket resumed successfully.',
    'ticket_closed' => 'Ticket closed successfully.',
    'ticket_pending_interval_deleted' => 'Pending interval deleted successfully.',

    'cid_created' => 'CID added successfully.',
    'cid_updated' => 'CID updated successfully.',
    'cid_deleted' => 'CID and all related tickets deleted successfully.',

    'user_created' => 'User added successfully.',
    'user_updated' => 'User updated successfully.',
    'user_deleted' => 'User deleted successfully.',

    'profile_updated' => 'Profile updated successfully.',

    'gamas_created' => 'GAMAS created with :total tickets.',
    'gamas_updated' => 'GAMAS updated successfully.',
    'gamas_closed' => 'GAMAS closed successfully.',
    'gamas_pending' => 'GAMAS set to pending.',
    'gamas_resumed' => 'GAMAS resumed successfully.',
    'gamas_interval_deleted' => 'Pending interval deleted successfully.',
    'gamas_ticket_removed' => 'Ticket removed from GAMAS.',
    'gamas_deleted' => 'GAMAS deleted successfully.',

    // Error notifications
    'gamas_ticket_remove_closed' => 'Cannot remove ticket from a closed GAMAS.',
    'user_self_delete' => 'You cannot delete your own account.',
    'ticket_closed_edit' => 'A closed ticket cannot be edited.',
];
