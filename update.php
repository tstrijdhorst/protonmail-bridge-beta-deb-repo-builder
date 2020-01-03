<?php

(new ProtonMailDebianRepoBuilder())->update();

class ProtonMailDebianRepoBuilder {
	private const PKGBUILD_URL = 'https://protonmail.com/download/beta/PKGBUILD';
	private const REPO_DIR     = __DIR__.'/repo';
	
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
	}
}