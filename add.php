public function addArticle(Request $request){
        $slug = str_slug($request->article_title);
        if(isset($request->article_image)) {
        $validator = Validator::make($request->all(), [
            'article_image' => 'mimes:jpg,jpeg,png,gif|max:2048'
        ]);
        if ($validator->fails()) {
             return response([
                'status' => 'error',
                'title' => 'Hata',
                'content' => 'Yüklediðiniz resmin uzantýsý jpg,jpeg,png,gif olmalý ve resim maksimumum 2 mb olmalýdýr.'
            ]);
        }
        //Resimi biçimlendirme
        $image = Input::file('article_image');
        $image_extension = Input::file('article_image')->getClientOriginalExtension();
        $image_name = $slug."-1200x800-".time().".".$image_extension;
        Storage::disk('uploads')->makeDirectory('img');
        Image::make($image->getRealPath())->resize(1200, 800)->save('uploads/img/'.$image_name);
        //****
        }
        try{
            if($request->article_title != null && $request->article_content != null ) {
                if ($request->article_rank == null || is_numeric($request->article_rank)) {
                    if($request->code_text == null){
                      $code_text = null;
                    } else {
                        $code_text = $request->code_text;
                    }
                        $article = Articles::create([
                            'article_title' => $request->article_title,
                            'article_category_id' => $request->article_category,
                            'article_content' => $request->article_content,
                            'article_slug' => $slug,
                            'code_text' => $code_text,
                            'article_rank' => $request->article_rank,
                            'article_status' => $request->status
                        ]);
                        if ($request->article_image == null) {
                            $path = null;
                            $image_name = null;
                        } else {
                            $path = '/uploads/img/' . $image_name;
                        }
                        ArticleDetails::create([
                            'article_id' => $article->article_id,
                            'article_image' => $path,
                            'article_alt_and_title' => $image_name,
                            'article_meta_title' => $slug,
                            'author' => $request->author,
                            'article_labels' => $request->article_labels,
                        ]);

                        $images = $request->file('images');
                        if (!empty($images)) {
                            $i = 1;
                            foreach ($images as $image) {
                                $image_extension = $image->getClientOriginalExtension();
                                if ($image_extension != "jpg" && $image_extension != "png" && $image_extension != "gif" && $image_extension != "jpeg") {
                                    return response([
                                        'status' => 'error',
                                        'title' => 'Hata',
                                        'content' => 'Yüklediðiniz resimlerin uzantýsý jpg,jpeg,png,gif olmalýdýr.'
                                    ]);
                                }
                                $image_name = $i . "." . $image_extension;
                                Storage::disk('uploads')->makeDirectory('img/blog/' . $slug);
                                Storage::disk('uploads')->put('img/blog/' . $slug . '/' . $image_name, file_get_contents($image));
                                $i++;
                                $path = '/uploads/img/blog/' . $slug . '/' . $image_name;
                                ArticleImages::create([
                                    'article_id' => $article->article_id,
                                    'path' => $path,
                                    'featured' => 0
                                ]);
                            }

                        }

                        return response(['status' => 'success', 'title' => 'Baþarýlý', 'content' => 'Baþarýyla Kaydedildi']);
                } else {
                    return response([
                        'status' => 'error',
                        'title' => 'Hata',
                        'content' => 'Sýralama Sayýsal Bir Deðer Olmalýdýr'
                    ]);
                }
            } else {
                return response([
                    'status' => 'error',
                    'title' => 'Hata',
                    'content' => 'Makale Baþlýðý ve Ýçeriði Boþ Geçilemez...'
                ]);
            }

        } catch (\Exception $e){
            return response(['status' => 'error', 'title' => 'Hata', 'content' => 'Kayýt'. str_limit($e,200) . 'Hatasýndan Dolayý Yapýlamadý']);
        }
}
