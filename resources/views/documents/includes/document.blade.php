<div class="mb-0 text-muted" data-docuemnt-container="true">
    <a href="{{ route('documents.download', $document->uuid) }}" target="_blank">{{ str_limit($document->name, 40) }}</a>
    <i class="fa fa-trash cursor-pointer text-danger mt-1 pull-right" onclick="customDocumentDelete(this)"></i> 
    <span class="pull-right">{{ bytesToSize($document->size) }}</span>
    
    {{ Form::open(['route' => ['documents.destroy', $document->uuid], 'method' => 'DELETE']) }}
    {{ Form::close() }}
</div>