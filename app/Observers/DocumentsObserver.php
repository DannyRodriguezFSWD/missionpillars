<?php

namespace App\Observers;

use App\Models\Document;
use Ramsey\Uuid\Uuid;

class DocumentsObserver 
{
    public function creating(Document $document) 
    {
        array_set($document, 'uuid', Uuid::uuid1());
    }
}
