<button class="button-small button-{{ $type ?? 'default' }}" type="button"
    @if(isset($action)) data-action="{{ $action }}" @endif
    @if(isset($id)) data-id="{{ $id }}" @endif
    @if(isset($indicator)) data-indicador="{{ $indicator }}" @endif
    @if(isset($image)) data-imagen="{{ $image }}" @endif
    @if(isset($form)) data-form-id="{{ $form }}" @endif>
    <span class="button__icon">
        @if($icon === 'edit')
            @include('components.svg-edit')
        @elseif($icon === 'delete')
            @include('components.svg-delete')
        @endif
    </span>
</button>
