/**
 * Utility functions for handling storage paths and URLs
 */

/**
 * Resolves a storage path to a full URL
 * Converts relative paths to /storage/ URLs while preserving absolute URLs
 *
 * @param path - The path to resolve (can be relative, absolute, or already a full URL)
 * @returns The resolved URL or null if path is falsy
 *
 * @example
 * resolveStoragePath('settings/logo.png') // returns '/storage/settings/logo.png'
 * resolveStoragePath('/storage/settings/logo.png') // returns '/storage/settings/logo.png'
 * resolveStoragePath('https://example.com/logo.png') // returns 'https://example.com/logo.png'
 * resolveStoragePath('') // returns null
 * resolveStoragePath(null) // returns null
 */
export function resolveStoragePath(path: string | null | undefined): string | null {
    // Return null for falsy values
    if (!path || path.trim() === '') {
        return null;
    }

    // Return as-is if it's already an absolute URL (http/https)
    if (path.startsWith('http://') || path.startsWith('https://')) {
        return path;
    }

    // Return as-is if it already starts with /storage
    if (path.startsWith('/storage/')) {
        return path;
    }

    // Convert relative path to storage URL
    return `/storage/${path}`;
}

/**
 * Checks if a path is a storage path (relative path that should be resolved to /storage/)
 *
 * @param path - The path to check
 * @returns True if the path should be resolved to a storage URL
 */
export function isStoragePath(path: string | null | undefined): boolean {
    if (!path || path.trim() === '') {
        return false;
    }

    // Not a storage path if it's already an absolute URL
    if (path.startsWith('http://') || path.startsWith('https://')) {
        return false;
    }

    // Not a storage path if it already starts with /storage
    if (path.startsWith('/storage/')) {
        return false;
    }

    // If it's a relative path, it's likely a storage path
    return true;
}

/**
 * Gets the filename from a storage path
 *
 * @param path - The storage path
 * @returns The filename or null if path is invalid
 *
 * @example
 * getStorageFilename('settings/logo.png') // returns 'logo.png'
 * getStorageFilename('/storage/settings/logo.png') // returns 'logo.png'
 * getStorageFilename('https://example.com/logo.png') // returns 'logo.png'
 */
export function getStorageFilename(path: string | null | undefined): string | null {
    if (!path || path.trim() === '') {
        return null;
    }

    const parts = path.split('/');
    return parts[parts.length - 1] || null;
}

/**
 * Gets the directory from a storage path
 *
 * @param path - The storage path
 * @returns The directory path or null if path is invalid
 *
 * @example
 * getStorageDirectory('settings/logo.png') // returns 'settings'
 * getStorageDirectory('/storage/settings/avatars/user.jpg') // returns 'settings/avatars'
 */
export function getStorageDirectory(path: string | null | undefined): string | null {
    if (!path || path.trim() === '') {
        return null;
    }

    // Remove /storage/ prefix if present
    const cleanPath = path.startsWith('/storage/') ? path.substring(9) : path;

    // Remove filename to get directory
    const parts = cleanPath.split('/');
    parts.pop(); // Remove filename

    return parts.length > 0 ? parts.join('/') : null;
}
