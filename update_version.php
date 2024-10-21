<?php
// Define the path to the version.php file.
$versionFilePath = __DIR__ . '/version.php';

// Read the current contents of the file.
$versionFileContents = file_get_contents($versionFilePath);

// Use regex to extract the current version number.
if (preg_match('/\$plugin->version\s*=\s*(\d+);/', $versionFileContents, $matches)) {
    $currentVersion = $matches[1];
    $currentDate = substr($currentVersion, 0, 8);
    $currentCounter = substr($currentVersion, 8);

    // Get today's date in the same format (YYYYMMDD).
    $today = date('Ymd');

    if ($today === $currentDate) {
        // If the date matches, increment the counter.
        $newCounter = str_pad((int)$currentCounter + 1, 2, '0', STR_PAD_LEFT);
    } else {
        // If the date is different, start a new counter.
        $newCounter = '01';
    }

    // Build the new version number.
    $newVersion = $today . $newCounter;

    // Replace the old version with the new one.
    $newVersionFileContents = preg_replace('/\$plugin->version\s*=\s*(\d+);/', "\$plugin->version = $newVersion;", $versionFileContents);

    // Write the new contents back to the file.
    file_put_contents($versionFilePath, $newVersionFileContents);

    echo "Updated version.php to version: $newVersion\n";
} else {
    echo "Could not find version in version.php\n";
}
?>
