<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', 'StudentController@index');
/**
 * faculty route
 */
Route::resource('faculties', 'FacultyController');
/**
 * student route
 */
Route::get('student/{id}/create_mark','StudentController@createMarks')->name('students.createMarks');
Route::resource('students', 'StudentController');

/**
 * class route
 */
Route::resource('classes', 'ClassController');
/**
 * subject route
 */
Route::resource('subjects', 'SubjectController');
/**
 * mark route
 */
Route::post('marks/store','MarkController@storeMore')->name('marks.storeMore');
Route::resource('marks','MarkController');
Route::get('marks/destroy/{id}','MarkController@destroy')->name('mark.post.destroy');

Route::group(['prefix' => 'marks/ajax'],function () {
    Route::get('subject/{studentId}','AjaxController@getSubject');
});

