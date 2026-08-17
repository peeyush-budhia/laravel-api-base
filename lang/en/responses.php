<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Request Responses
    |--------------------------------------------------------------------------
    */

    'success' => 'Request completed successfully.',
    'created' => 'Resource created successfully.',
    'updated' => 'Resource updated successfully.',
    'deleted' => 'Resource deleted successfully.',
    'restored' => 'Resource restored successfully.',
    'status_changed' => 'Status updated successfully.',

    'too_many_requests' => 'Too many requests. Please try again later.',

    'login_success' => 'User logged in successfully',
    'logout_success' => 'User logged out successfully',

    /*
    |--------------------------------------------------------------------------
    | Client Errors
    |--------------------------------------------------------------------------
    */

    'bad_request' => 'Bad request.',
    'validation_failed' => 'Validation failed.',
    'unauthorized' => 'Authentication is required to access this resource.',
    'forbidden' => 'You do not have permission to perform this action.',
    'not_found' => 'The requested resource was not found.',
    'conflict' => 'The request could not be completed due to a conflict.',

    /*
    |--------------------------------------------------------------------------
    | Server Errors
    |--------------------------------------------------------------------------
    */

    'server_error' => 'An unexpected error occurred. Please try again later.',

    /*
    |--------------------------------------------------------------------------
    | Password Reset
    |--------------------------------------------------------------------------
    */
    'password_reset_link_sent' => 'If an account exists for that email address, a password reset link has been sent.',

    'password_reset_success' => 'Your password has been reset successfully.',

    'password_changed' => 'Password changed successfully.',

    /*
    |--------------------------------------------------------------------------
    | Manage super-admin and self account
    |--------------------------------------------------------------------------
    */
    'user_cannot_delete_self' => 'You cannot delete your own account.',
    'super_admin_delete_forbidden' => 'You cannot delete a Super Admin account.',

    'user_cannot_change_own_role' => 'You cannot change your own role.',
    'super_admin_role_change_forbidden' => 'Only a Super Admin can change the role of a Super Admin.',

    'user_cannot_manage_self' => 'You cannot manage your own account.',
    'super_admin_manage_forbidden' => 'Super Admin accounts cannot be modified.',

];
