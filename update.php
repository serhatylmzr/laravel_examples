public function updateArticle(Request $request){
$slug = str_slug($request->article_title);
if(isset($request->article_image)) {
$validator = Validator::make($request->all(), [
'article_image' => 'mimes:jpg,jpeg,png,gif|max:2048'
]);
if ($validator->fails()) {
return response([
'status' => 'error',
'title' => 'Hata',
'content' => 'Yüklediğiniz resmin uzantısı jpg,jpeg,png,gif olmalı ve resim maksimumum 2 mb olmalıdır.'
]);
}

$oldimage = ArticleDetails::where('article_id', $request->article_id)->first()->article_image;
if ($oldimage != null) {
$image_path = public_path($oldimage);
if (file_exists($image_path)) {
unlink($image_path);
}
}
$image = Input::file('article_image');
$image_extension = Input::file('article_image')->getClientOriginalExtension();
$image_name = $slug."-1200x800".time().".".$image_extension;
Storage::disk('uploads')->makeDirectory('img');
Image::make($image->getRealPath())->resize(1200, 800)->save('uploads/img/'.$image_name);
}
try{
if($request->article_title != null && $request->article_content != null ) {
if ($request->article_rank == null || is_numeric($request->article_rank)) {
if($request->code_text == null){
$code_text = null;
} else {
$code_text = $request->code_text;
}
$article = Articles::where('article_id', $request->article_id)->update([
'article_title' => $request->article_title,
'article_category_id' => $request->article_category,
'article_content' => $request->article_content,
'code_text' => $code_text,
'article_slug' => $slug,
'article_rank' => $request->article_rank,
'article_status' => $request->status
]);
if($request->article_image == null){
$article_detail = ArticleDetails::where('article_id', $request->article_id)->first();
$path =  $article_detail->article_image;
$image_name = $article_detail->article_alt_and_title;
} else {
$path = '/uploads/img/'.$image_name;
}
ArticleDetails::where('article_id', $request->article_id)->update([
'article_image' => $path,
'article_alt_and_title' => $image_name ,
'article_meta_title' => $slug,
'author' => $request->author,
'article_labels' => $request->article_labels,
]);

$images = $request->file('images');
if(!empty($images)){
$oldimages = ArticleImages::where('article_id', $request->article_id)->get();

if (count($oldimages) > 0) {
foreach($oldimages as $oldimage){
$oldimage->delete();
}
$directory =  public_path("uploads/img/blog/".$slug);
Storage::disk('uploads')->deleteDirectory('img/blog/'.$slug);
}
$i = 1;
foreach($images as $image){

$image_extension = $image->getClientOriginalExtension();
if($image_extension != "jpg" && $image_extension != "png" && $image_extension != "gif" && $image_extension != "jpeg"){
return response([
'status' => 'error',
'title' => 'Hata',
'content' => 'Yüklediğiniz resimlerin uzantısı jpg,jpeg,png,gif olmalıdır.'
]);
}
$image_name = $i."-".time().".".$image_extension;
Storage::disk('uploads')->makeDirectory('img/blog/'.$slug);
Storage::disk('uploads')->put('img/blog/'.$slug.'/'.$image_name, file_get_contents($image));
$i++;
$path =  '/uploads/img/blog/'.$slug.'/'.$image_name;
ArticleImages::create([
'article_id' => $request->article_id,
'path' => $path,
'featured' => 0
]);
}
}
return response(['status' => 'success', 'title' => 'Başarılı', 'content' => 'Başarıyla Düzenlendi']);
} else {
return response([
'status' => 'error',
'title' => 'Hata',
'content' => 'Sıralama Sayısal Bir Değer Olmalıdır'
]);
}
} else {
return response([
'status' => 'error',
'title' => 'Hata',
'content' => 'Makale Başlığı ve İçeriği Boş Geçilemez...'
]);
}

} catch (\Exception $e){
return response(['status' => 'error', 'title' => 'Hata', 'content' => 'Kayıt'. str_limit($e,500) . 'Hatasından Dolayı Yapılamadı']);
}

}