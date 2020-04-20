@php
  $isInvalid = $errors->has($key);
@endphp

<div class="form__field {{ ($isInvalid ? "form__field_is-invalid" : "")}}">
  
  <label class="form__label" for="{{$key}}">{{$name}}</label>

  <?php if ($type === "file") { ?>
    <input class="filepicker" type="file" name="{{$key}}" id="{{$key}}"/>
  <?php } else {?>
    <input class="textbox" type="text" name="{{$key}}" value="{{$value ?? ""}}" id="{{$key}}" autocomplete="off"/>
  <?php } ?>
  
  @error($key)
    <div class="alert alert-danger">{{ $message }}</div>
  @enderror

</div>
