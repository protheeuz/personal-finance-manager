<?php

/**
 * Class MigrateController
 */
class MigrateController extends BaseController
{

    /**
     * @return $this
     */
    public function index()
    {
        return View::make('migrate.index')->with('index', 'Migration');
    }

    /**
     *
     */
    public function upload()
    {
        if (Input::hasFile('file') && Input::file('file')->isValid()) {
            $path     = storage_path();
            $fileName = 'firefly-iii-import-' . date('Y-m-d-H-i') . '.json';
            $fullName = $path . DIRECTORY_SEPARATOR . $fileName;
            if (Input::file('file')->move($path, $fileName)) {
                // so now Firefly pushes something in a queue and does something with it! Yay!
                \Log::debug('Pushed a job to start the import.');
                Queue::push('Firefly\Queue\Import@start', ['file' => $fullName, 'user' => \Auth::user()->id]);
                if (Config::get('queue.default') == 'sync') {
                    Session::flash('success', 'Your data has been imported!');
                } else {
                    Session::flash(
                        'success',
                        'The import job has been queued. Please be patient. Data will appear slowly. Please be patient.'
                    );
                }

                return Redirect::route('index');
            }
            Session::flash('error', 'Could not save file to storage.');
            return Redirect::route('migrate.index');

        } else {
            Session::flash('error', 'Please upload a file.');
            return Redirect::route('migrate.index');
        }

    }

} 