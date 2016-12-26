<?php
/**
 *      Files and directories
 */
Route::group(['prefix' => 'filemanager'],function(){

    //Display file manager page
    Route::get('files','Xerobase\Filemanager\FileManagerController@index');

    //Remove a file or directory
    Route::post('removefile','Xerobase\Filemanager\FileManagerController@removeFile');

    //Open a directory
    Route::post('opendirectory','Xerobase\Filemanager\FileManagerController@openDirectory');

    //Create a directory
    Route::post('makenewdirectory','Xerobase\Filemanager\FileManagerController@makeNewDirectory');

    //Upload a file
    Route::post('uploadfile','Xerobase\Filemanager\FileManagerController@uploadFile');

    //Delete multiple files and directories using checkbox
    Route::post('deleteMultipleFiles','Xerobase\Filemanager\FileManagerController@deleteMultiple');

    //Rename files and directories
    Route::post('renameFiles','Xerobase\Filemanager\FileManagerController@renameFiles');

    //Move files and directories
    Route::post('movefiles','Xerobase\Filemanager\FileManagerController@moveFiles');

    // Creating download links
    Route::get('file/{real_name}','Xerobase\Filemanager\FileManagerController@fileLink');

});
