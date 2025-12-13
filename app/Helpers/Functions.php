<?php

namespace App\Helpers;

/**
 * HTML escape helper, UTF-8 safe.
 *
 * @param string|null $v
 * @return string
 */
function e($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/**
 * Get the correct URL path for a profile picture.
 * Handles various storage formats and provides a default fallback.
 *
 * @param string|null $profilePicture The profile_picture value from the database
 * @param string $default The default image path if no profile picture exists
 * @return string The correct URL path for the image
 */
function profilePicturePath(?string $profilePicture, string $default = '/images/avatar-default.svg'): string
{
    // Return default if empty
    if (empty($profilePicture)) {
        return $default;
    }

    // Already has a leading slash - it's a full path
    if (str_starts_with($profilePicture, '/')) {
        return $profilePicture;
    }

    // Just a filename - prepend /uploads/
    return '/uploads/' . $profilePicture;
}
