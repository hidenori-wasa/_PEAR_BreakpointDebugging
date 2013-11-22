function test()
{
    var $head = document.getElementsByTagName('head')[0];
    var $scripts = document.getElementsByTagName('script');
    for($count = $scripts.length - 1; $count >= 0; $count--){
        $head.removeChild($scripts[$count]);
    }
}
test();
