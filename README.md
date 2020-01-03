# Protonmail Bridge Beta Local Debian Repo Builder

## What does it do?

It downloads the latest .deb for the protonmail bridge beta 
and it adds it to a local debian repository so it will automatically update via the normal route.

## Why?

Because installing packages from my email is not my preferred way of installing packages (ahum... protonmail?).

## HOW DO I SHOT WEB?

*Tested on ubuntu 19.10*

1. Clone this repo
2. Generate a gpg key for signing with a name of your choice e.g `ProtonMailRepoSignKey`
3. Make sure the public key is imported into apt:

    ```
    gpg --list ProtonmailRepoSignKey```
    gpg -a --export *FILL IN YOUR KEY ID* | sudo apt-key add -
   ```
4. Add the local repo to your apt by appending `deb file:/your/repo/path/repo ./` to `/etc/apt/sources.list`
5. Run `update.php ProtonMailRepoSignKey`

You should now be able to do install / update the `protonmail-bridge` as you would do with any other apt repo.

## Caveats

1. If the repo dir is not readable by the `apt` user then you will get a message that the package will be downloaded unsandboxed as root like so:

```
Download is performed unsandboxed as root as file '/home/totallyRealUsername/proton-mail-bridge-repo/repo/./protonmail-bridge_1.2.4-1_amd64.deb' couldn't be accessed by user '_apt'. - pkgAcquire::Run (13: Permission denied)
```

## Why did you make this in PHP?

Next question