# xerobase/filemanager
Filemanager package for Laravel 5
Features such as creating directories or subdirectories uploading files(currently images/pdf but customizable)
This package is fully customizable you can apply any changes you might gonna need.
It is developed on laravel 5.3 so it works perfectly on this version but shouldn't have problems with lower versions.
## Installation
1. Run this command.
  * `composer require xerobase/filemanager dev-master`
2. Then add this provider to your `config/app.php` file.

  ```
      Xerobase\Filemanager\FileManagerServiceProvider::class,
  ```
3. Then run `php artisan vendor:publish`

4. And finally run `php artisan migrate`

Now you can enjoy your new filemanager!

## Basic Usage
After successfully installing filemanager it is accessible by visiting this route : 

  **http://{your laravel project url}/filemanager/files**

Of course you can change it however you want.

All files and directories are stored inside **file_manager** directory,under your project **storage/app** directory.

This package uses database to store any needed information about directories structure, files format and etc.

table name is **filemanagers**.
## Notes
This package is fully customizable, it uses lots of jQuery to do all the ajax requests.

You can change the view file(change template) or any part of backend logic in it's controller file.

## License
This package **xerobase/filemanager** is created by [Armin Abbasi](http://xerobase.pro) and is released under MIT License.
