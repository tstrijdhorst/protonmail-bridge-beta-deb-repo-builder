<?php

(new ProtonMailDebianRepoBuilder())->update();

class ProtonMailDebianRepoBuilder {
	private const PKGBUILD_URL  = 'https://protonmail.com/download/beta/PKGBUILD';
	private const REPO_DIR      = __DIR__.'/repo';
	private const GPG_SIGN_USER = 'ProtonMailRepoSignKey';
	
	public function update() {
		preg_match('/source=\("(.*)"\)/', file_get_contents(self::PKGBUILD_URL), $matches);
		
		if (count($matches) < 2) {
			throw new Exception('PKGBUILD file is corrupted');
		}
		
		$latestDebFileURL  = $matches[1];
		$latestDebFileName = basename($latestDebFileURL);
		$latestDebFilePath = self::REPO_DIR.'/'.$latestDebFileName;
		
		//Apparently we already have the latest version
		if (file_exists($latestDebFilePath)) {
			echo 'Already at latest version'.PHP_EOL;
			return;
		}
		
		echo 'Update found'.PHP_EOL;
		echo 'Downloading: '.$latestDebFileURL.PHP_EOL;
		
		file_put_contents($latestDebFilePath, fopen($latestDebFileURL, 'r'));
		
		echo 'Download finished'.PHP_EOL;
		
		echo 'Updating local apt repo'.PHP_EOL;
		chdir(self::REPO_DIR);
		exec('dpkg-scanpackages . /dev/null > Packages');
		exec('gzip --keep --force -9 Packages');
		exec('apt-ftparchive release . > Release');
		exec('gpg --clearsign -o InRelease --local-user '.escapeshellarg(self::GPG_SIGN_USER).' Release');
		exec('gpg -abs -o Release.gpg --local-user '.escapeshellarg(self::GPG_SIGN_USER).' Release');
		
		echo 'Local apt repo updated. Run apt-get update && apt-get upgrade to install new version'.PHP_EOL;
	}
}