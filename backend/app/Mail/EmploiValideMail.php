<?php

namespace App\Mail;

use App\Models\Emploi;
use Illuminate\Mail\Mailable;

class EmploiValideMail extends Mailable
{
    public $emploi;

    public function __construct(Emploi $emploi)
    {
        $this->emploi = $emploi;
    }

    public function build()
    {
        return $this->subject('Emploi validé')
                    ->view('emails.emploi_valide');
    }
}
