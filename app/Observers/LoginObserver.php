<?php

namespace App\Observers;

use App\Models\LoginSession;
use App\Models\Counter;

class LoginObserver
{
    /**
     * Handle the LoginSession "created" event.
     *
     * @param  \App\Models\LoginSession  $loginSession
     * @return void
     */
    public function created(LoginSession $loginSession)
    {
        // Created is called when the login model is inserted into the database
        $counter = $loginSession->counter;

        // if the login session model has a login time (and does not have a logout time) and associated counter, turn the found counter to active i.e. True
        if ($loginSession->login_date_time && is_null($loginSession->logout_date_time) && $counter) {
            Counter::find($counter->id)->update([
                'is_active' => true
            ]);

        // if the login session model has a logout time (does not matter if login time is available or not), turn that counter to not active i.e False
        } elseif ($loginSession->logout_date_time && $counter) {
            Counter::find($counter->id)->update([
                'is_active' => false
            ]);
        }
    }

    /**
     * Handle the LoginSession "updated" event.
     *
     * @param  \App\Models\LoginSession  $loginSession
     * @return void
     */
    public function updated(LoginSession $loginSession)
    {
        // Typically used for updating existing model to change counter to not active,
        // Usually should have counter attribute already active in LoginSession model
        $counter = $loginSession->counter;

        if ($loginSession->logout_date_time) {
            Counter::find($counter->id)->update([
                'is_active' => false
            ]);
        } elseif ($loginSession->login_date_time) {
            Counter::find($counter->id)->update([
                'is_active' => true
            ]);
        }
    }

    /**
     * Handle the LoginSession "deleted" event.
     *
     * @param  \App\Models\LoginSession  $loginSession
     * @return void
     */
    public function deleted(LoginSession $loginSession)
    {
        //
    }

    /**
     * Handle the LoginSession "restored" event.
     *
     * @param  \App\Models\LoginSession  $loginSession
     * @return void
     */
    public function restored(LoginSession $loginSession)
    {
        //
    }

    /**
     * Handle the LoginSession "force deleted" event.
     *
     * @param  \App\Models\LoginSession  $loginSession
     * @return void
     */
    public function forceDeleted(LoginSession $loginSession)
    {
        //
    }
}
