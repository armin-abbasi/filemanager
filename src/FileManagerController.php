<?php

namespace Xerobase\Filemanager;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Xerobase\Filemanager\Filemanager;

class FileManagerController extends Controller
{
    public function index(){
        $files = Filemanager::orderBy('type','asc')->where('path','')->get();
        return view('Filemanager::index',['files' => $files]);
    }

    /**
     *      Remove a file or directory
     */
    public function removeFile(Request $request){
        if($request->has('fileID')){
            $id = $request->input('fileID');
            $file = Filemanager::find($id);
            $path = 'file_manager/';
            if($file->path != '')
                $path = $path.'/'.$file->path;
            $path = $path.'/'.$file->real_name;
            if(Storage::exists($path)){
                if($file->type == 'dir'){
                    $result = Storage::deleteDirectory($path);
                    if($result){
                        $real_name = $file->real_name;
                        $parent = Filemanager::where('real_name', '=', $real_name)->delete();
                        if($parent){
                            Filemanager::where('path', 'like', "%$real_name%")->delete();
                            // Update parent directories , sub-directories count
                            if($file->path != '') {
                                $parents = explode('/', $file->path);
                                foreach ($parents as $parent) {
                                    $item = Filemanager::where('real_name', $parent)->first();
                                    if (!empty($item)) {
                                        $path = 'file_manager/'.$item->path.'/'.$item->real_name;
                                        $subFilesCount = count(Storage::files($path));
                                        $subDirCount = count(Storage::directories($path));
                                        $item->sub_folders = $subDirCount;
                                        $item->sub_files = $subFilesCount;
                                        $item->save();
                                    }
                                }
                            }
                            return 'success';
                        }
                    }
                }else{
                    $result = Storage::delete($path);
                    if($result){
                        $real_name = $file->real_name;
                        $removeFile = Filemanager::where('real_name', '=', $real_name)->delete();
                        if($removeFile){
                            // Update parent directories , sub-directories count
                            if($file->path != '') {
                                $parents = explode('/', $file->path);
                                foreach ($parents as $parent) {
                                    $item = Filemanager::where('real_name', $parent)->first();
                                    if (!empty($item)) {
                                        $item->sub_files -= 1;
                                        $item->save();
                                    }
                                }
                            }
                            return 'success';
                        }
                    }
                }
            }
        }
        return 'failure';
    }

    /**
     *      Open a directory
     */
    public function openDirectory(Request $request){
        $path = $request->input('path');
        $files = Filemanager::where('path',$path)->orderBy('type','asc')->get();
        return $files;
    }

    /**
     *      Create a directory
     */
    public function makeNewDirectory(Request $request){
        if($request->has('name')){
            $data['path'] = $request->input('path');
            $data['name'] = $request->input('name');
            $checkName = $data['name'];
            $data['real_name'] = time();
            $pathToCreate = 'file_manager/'.$data['path'].'/'.$data['real_name'];
            $data['date'] = date('Y-m-d');
            $data['type'] = 'dir';
            $count = 0;
            if(Storage::makeDirectory($pathToCreate,0777,true)){
                // Update parent directories , sub-directories count
                if($data['path'] != '' || $data['path'] != null) {
                    $parents = explode('/',$data['path']);
                    foreach ($parents as $parent) {
                        $item = Filemanager::where('real_name', $parent)->first();
                        $item->sub_folders += 1;
                        $item->save();
                    }
                }
                while(Filemanager::where('path',$data['path'])->where('name',$checkName)->get()->count() > 0){
                    $count++;
                    $checkName = $data['name'].'('.$count.')';
                }
                $data['name'] = $checkName;
                $result = Filemanager::create($data);
                if($result)
                    return $result;
            }
        }
        return 'failure';
    }

    /**
     *      Upload a file
     */
    public function uploadFile(Request $request){
        $fileType = $request->file->getClientMimeType();
        if($fileType == 'application/pdf' || $fileType == 'image/png' || $fileType == 'image/jpeg') {
            $data['path'] = $request->input('path');
            $pathToMove = 'file_manager/'. $data['path'];
            $data['name'] = $request->file->getClientOriginalName();
            $checkName = $data['name'];
            $data['extension'] = strtolower($request->file->getClientOriginalExtension());
            $data['size'] = $request->file->getClientSize() / 1000;
            if($data['size'] > 4000)
                return 'Your file is larger than limit(2MB)!';
            $data['real_name'] = time() . '.' . $data['extension'];
            $data['date'] = date('Y-m-d');
            $data['type'] = 'file';
            $count = 0;
            if ($data['real_name']) {
                $moved = $request->file->move(storage_path($pathToMove), $data['real_name']);
                if ($moved) {
                    // Update parent directories , sub-directories count
                    if($data['path'] !== '') {
                        $parents = explode('/', $data['path']);
                        foreach ($parents as $parent) {
                            $item = Filemanager::where('real_name', $parent)->first();
                            if (!empty($item)) {
                                $item->sub_files += 1;
                                $item->save();
                            }
                        }
                    }
                    while(Filemanager::where('path',$data['path'])->where('name',$checkName)->get()->count() > 0){
                        $count++;
                        $checkName = $data['name'].'('.$count.')';
                    }
                    $data['name'] = $checkName;
                    $result = Filemanager::create($data);
                    return $result;
                }
            }
        }else{
            $msg = ['error' => 'This file type is not supported!'];
            return response()->json($msg);
        }
        $msg = ['error' => 'Upload was not successful!'];
        return response()->json($msg);
    }

    /**
     *      Delete multiple files and directories using checkbox
     */
    public function deleteMultiple(Request $request)
    {
        if ($request->has('IDs')) {
            $IDs = $request->input('IDs');
            foreach ($IDs as $index => $ID) {
                $file = Filemanager::find($ID);
                $path = 'file_manager/';
                if($file->path != '')
                    $path = $path.'/'.$file->path;
                $path = $path.'/'.$file->real_name;
                if (Storage::exists($path)) {
                    if ($file->type == 'dir') {
                        $result = Storage::deleteDirectory($path);
                        if ($result) {
                            $real_name = $file->real_name;
                            $parent = Filemanager::where('real_name', '=', $real_name)->delete();
                            if ($parent) {
                                Filemanager::where('path', 'like', "%$real_name%")->delete();
                                // Update parent directories , sub-directories count
                                if ($file->path != '') {
                                    $parents = explode('/', $file->path);
                                    foreach ($parents as $parent) {
                                        $item = Filemanager::where('real_name', $parent)->first();
                                        if (!empty($item)) {
                                            $path = 'file_manager/'.$item->path.'/'.$item->real_name;
                                            $subFilesCount = count(Storage::files($path));
                                            $subDirCount = count(Storage::directories($path));
                                            $item->sub_folders = $subDirCount;
                                            $item->sub_files = $subFilesCount;
                                            $item->save();
                                        }
                                    }
                                }
                                $finalResult[$index] = 'success';
                            }
                        }
                    } else {
                        $result = Storage::delete($path);
                        if ($result) {
                            $real_name = $file->real_name;
                            $removeFile = Filemanager::where('real_name', '=', $real_name)->delete();
                            if ($removeFile) {
                                // Update parent directories , sub-directories count
                                if ($file->path != '') {
                                    $parents = explode('/', $file->path);
                                    foreach ($parents as $parent) {
                                        $item = Filemanager::where('real_name', $parent)->first();
                                        if (!empty($item)) {
                                            $item->sub_files -= 1;
                                            $item->save();
                                        }
                                    }
                                }
                                $finalResult[$index] = 'success';
                            }
                        }
                    }
                }
            }
        }
        if(empty($finalResult))
            return 'failure';
        else
            return $finalResult;
    }

    /**
     *      Rename files or directories
     */
    public function renameFiles(Request $request){
        if($request->has('newName')){
            $newName = $request->input('newName');
            $id = $request->input('id');
            $file = Filemanager::find($id);
            $file->name = $newName;
            $result = $file->save();
            if($result){
                return 'success';
            }
        }
        return 'failure';
    }

    /**
     *      Move files and directories
     */
    public function moveFiles(Request $request){
        if($request->has('moveToID') && $request->has('IDs')) {
            $moveID = $request->input('moveToID');
            $IDs = $request->input('IDs');
            $moveTO = Filemanager::find($moveID);
            foreach ($IDs as $parentIndex => $ID) {
                $parent = Filemanager::find($ID);

                // If it's moving into the same directory , naughty user!
                if($parent->path == '') {
                    if ($parent->real_name == $moveTO->real_name)
                        continue;
                }

                // if it's already in the same root directory
                if($parent->path == $moveTO->real_name)
                    continue;

                // First move the selected directory
                $currentPath = 'file_manager/' . $parent->path . '/' . $parent->real_name;
                $newPath = 'file_manager/' . $moveTO->real_name . '/' . $parent->real_name;
                if (Storage::move($currentPath, $newPath)) { // If directory moved update all of it's children path
                    if ($parent->type == 'dir') {
                        // Update moving directory's root sub files and folders count
                        $movingPathArray = explode('/',$parent->path);
                        foreach($movingPathArray as $movingPath){
                            $root = Filemanager::where('real_name',$movingPath)->first();
                            if(!empty($root)) {
                                $root->sub_folders -= $parent->sub_folders + 1;
                                $root->sub_files -= $parent->sub_files;
                                $root->save();
                            }
                        }
                        // If it's a directory and has children
                        $children = Filemanager::where('path', 'like', "%$parent->real_name%")->get();
                        if (!empty($children)) {
                            foreach ($children as $index => $child) {
                                // Remove the root directory from path
                                $path = $child->path;
                                $path_array = explode('/', $path);
                                foreach ($path_array as $key => $value) {
                                    if ($value == $parent->real_name) {
                                        $path_sliced[$index] = array_slice($path_array, $key);
                                        continue;
                                    }
                                }
                                if (!empty($path_sliced)) {
                                    foreach ($path_sliced as $key => $value) {
                                        $child_path[$index] = implode('/', $value);
                                        $child_path[$index] = $moveTO->real_name . '/' . $child_path[$index];
                                    }
                                    if (!empty($child_path[$index])) {
                                        $updated_children[$index] = Filemanager::find($child->id);
                                        $updated_children[$index]->path = $child_path[$index];
                                        $updated_children[$index]->save();
                                    }
                                }

                            }
                        }
                        // Update the directories path itself
                        $parent->path = $moveTO->real_name;
                        $result = $parent->save();

                        // Gather count of sub files and directories of moving elements
                        $moveFilesCount[$parentIndex]['files'] = $parent->sub_files;
                        $moveFilesCount[$parentIndex]['dirs'] = $parent->sub_folders + 1;
                    } else {
                        // It's a file

                        // Update source root directories sub folders and sub files count
                        if(!empty($movingPathArray)) {
                            $movingPathArray = explode('/', $parent->path);
                            foreach ($movingPathArray as $movingPath) {
                                $root = Filemanager::where('real_name', $movingPath)->first();
                                $root->sub_files -= 1;
                                $root->save();
                            }
                        }

                        // Update destination folder's sub folders and sub files count
                        $parent->path = $moveTO->real_name;
                        $result = $parent->save();
                        $moveFilesCount[$parentIndex]['files'] = 1;
                        $moveFilesCount[$parentIndex]['dirs'] = 0;
                    }

                } else {
                    return 'ÎØÇ?? ÈæÌæÏ ÂãÏå , áØÝÂ ãÌÏÏ ÓÚ? ˜ä?Ï';
                }
            }
            // Update 'Move TO' directory sub files and folders count
            $movePath = 'file_manager/' . $moveTO->path . '/' . $moveTO->real_name;
            $sub_files = 0;
            $sub_folders = 0;
            if(!empty($moveFilesCount)) {
                foreach ($moveFilesCount as $item) {
                    $sub_files += $item['files'];
                    $sub_folders += $item['dirs'];
                }
            }
            $moveTO->sub_files = $moveTO->sub_files + $sub_files;
            $moveTO->sub_folders = $moveTO->sub_folders + $sub_folders;
            $moveTO->save();
        }else{
            return 'áØÝÂ ÑæäÏå åÇ? ãæÑÏ äÙÑ ÑÇ Èå åãÑÇå ãÞÕÏ ÇäÊÞÇá ãÔÎÕ ˜ä?Ï';
        }
        return 'success';
    }


    /**
     *      File download and view link
     */
    public function fileLink($real_name){
        $file = Filemanager::where('real_name',$real_name)->first();
        if(empty($file->path))
            $path = 'file_manager/'.$file->real_name;
        else
            $path = 'file_manager/'.$file->path.'/'.$file->real_name;
        $link = storage_path($path);
        $file->downloads += 1;
        $file->save();
        return response()->download($link);
    }
}
