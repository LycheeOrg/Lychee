api.post = function (fn, params, callback) {

let data = '';
let ok = false;
console.log(fn);
@foreach($functions as $function)
if(fn == '{!!  $function['name'] !!}')
{
@if($function['type'] == 'string')
    data = `{!! $function['data'] !!}`
    ok = true;
@else
    let ok = false;
@foreach($function['array'] as $item)
    if(params.{!! $function['kind'] !!} == '{!! $item['id'] !!}')
    {
        data = `{!! $item['data'] !!}`
        ok = true
    }
@endforeach
    if(!ok) {
        alert('this is just a demo : {!! $function['kind'] !!} unknown');
        ok = true; // at least we found the function. :)
    }
@endif
}
@endforeach

if(!ok)
{
    alert('this is just a demo : function unknown');
}
if(data != '')
{
    console.log(data);
    callback(JSON.parse(data));
}
