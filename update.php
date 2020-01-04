<?php

const PKGBUILD_URL  = 'https://protonmail.com/download/beta/PKGBUILD';
const REPO_DIR      = __DIR__.'/repo';

if ($argc < 2) {
	echo 'Usage: php '.$argv[0].' <gpg-sign-key-user>'.PHP_EOL;
	exit(1);
}
$gpgSignKeyUser = escapeshellarg($argv[1]);

$PKGBUILDFile = file_get_contents(PKGBUILD_URL);
preg_match('/source=\("(.*)"\)/', $PKGBUILDFile, $matches);
if (count($matches) < 2) {
	throw new Exception('PKGBUILD file is corrupted');
}

$latestDebFileURL  = $matches[1];
$latestDebFileName = basename($latestDebFileURL);
$latestDebFilePath = REPO_DIR.'/'.$latestDebFileName;

//Apparently we already have the latest version
if (file_exists($latestDebFilePath)) {
	echo 'Already at latest version'.PHP_EOL;
	return;
}

echo 'Update found'.PHP_EOL;
echo 'Downloading: '.$latestDebFileURL.PHP_EOL;

file_put_contents($latestDebFilePath, fopen($latestDebFileURL, 'r'));

echo 'Download finished'.PHP_EOL;

echo 'Calculating and comparing checksum'.PHP_EOL;
unset($matches);
preg_match('/sha256sums=\("(.*)"\)/', $PKGBUILDFile, $matches);
if (count($matches) < 2) {
	throw new Exception('PKGBUILD file is corrupted');
}

$checksumSHA256 = $matches[1];
if (hash_file( 'sha256', $latestDebFilePath) !== $checksumSHA256) {
	unlink($latestDebFilePath);
	throw new Exception('Checksum does not match');
}
echo 'Checksum matches'.PHP_EOL;

echo 'Updating local apt repo'.PHP_EOL;
chdir(REPO_DIR);
exec('dpkg-scanpackages . /dev/null > Packages');
exec('gzip --keep --force -9 Packages');
exec('apt-ftparchive release . > Release');
exec('gpg --clearsign -o InRelease --local-user '.$gpgSignKeyUser.' Release');
exec('gpg -abs -o Release.gpg --local-user '.$gpgSignKeyUser.' Release');

echo 'Local apt repo updated. Run apt-get update && apt-get upgrade to install new version'.PHP_EOL;