<?php

namespace App\Repositories\Eloquents;

use App\Facades\HandleImageService;
use App\Movie;
use App\Repositories\Contracts\MovieInterface;
use DB;
class MovieRepository extends BaseRepository implements MovieInterface {

    private static $with = ['images', 'genreses'];
    public function model(){
        return new Movie;
    }

    public function all($with = []){

    }

    public function getData($with = [], $condition = [], $dataSelect = ['*']) {
        return $this->model()
            ->select($dataSelect)
            ->with(static::$with)
            ->paginate(5);
    }

    public function getDataWithTrash($with = [], $condition = [], $dataSelect = ['*']) {
        return $this->model()
            ->select($dataSelect)
            ->onlyTrashed()
            ->with(static::$with)
            ->paginate(5);
    }

    public function create($data){
        try{
            DB::beginTransaction();
            $poster_path = HandleImageService::addImage($data['poster'], config('settings.movie.poster'));
            $movie = $this->model()->create($this->getParamMovie($data, $poster_path, $poster_path));
            $this->handleImageDetails($data, $movie);
            $this->handleRelations($data, $movie);
            DB::commit();

            return ['status_code' => 200, 'message' => 'Tạo thành công'];
        }catch(\Exception $e){
            DB::rollback();
            return ['status_code' => 500, 'message' => $e->getMessage()];
        }
    }

    public function update($id, $data){
        try{
            DB::beginTransaction();
            $movie = $this->find($id);
            $imageDeletes = [];
            $poster_path = HandleImageService::addImage($data['poster'] ?? false, config('settings.movie.poster'));
            if($data['image_ids']){
                $images = $movie->images()->whereIn('id', $data['image_ids'])->get();
                foreach($images as $image){
                    array_push($imageDeletes, json_decode($image['posters'], true)['file_path']);
                }
                $images->each->delete();
            }
            array_push($imageDeletes, $movie->poster_path);
            $this->handleImageDetails($data, $movie);
            $this->handleRelations($data, $movie);
            $movie->update($this->getParamMovie($data, $poster_path, $poster_path));
            HandleImageService::deleteImage($imageDeletes);
            DB::commit();

            return ['status_code' => 200, 'message' => 'Cập nhật thành công'];
        }catch(\Exception $e){
            DB::rollback();
            return ['status_code' => 500, 'message' => $e->getMessage()];
        }
    }

    public function find($id, $with = [])
    {
        return $this->model()
                    ->with($with)
                    ->findOrFail($id);
    }

    public function destroy($id){
        $movie = $this->model()->find($id);
        return $movie->delete();
    }

    public function search($term, $with = []){
        return $this->model()::search($term)
            ->with($with)
            ->where('title', 'LIKE', '%' . $term . '%')
            ->orWhere('original_title', 'LIKE', '%' . $term . '%')
            ->paginate(10);
    }

    private function getParamMovie($data, $poster_path = '', $backdrop_path = ''){
        if(strlen($poster_path) > 0){
            $data['poster_path'] = $poster_path;
            $data['backdrop_path'] = $backdrop_path;
        }
        $data['popularity'] = 0;
        $data['spoken_languages'] = 'EN, Vi';
        return $data;
    }

    private function handleImageDetails($data, $movie){
        if($data['images'] ?? false){
            foreach($data['images'] as $image){
                $path = HandleImageService::addImage($image, [500, 280]);
                $image = json_encode([
                    'original_name' => $image->getClientOriginalName(),
                    'file_path' => $path,
                    'height' => 500,
                    'width' => 280,
                    'vote_average' => 0,
                    'vote_count'=> 0,
                    'type' => $image->getClientMimeType() .'.'. $image->extension(),
                    'size' => $image->getSize(),
                ]);
                $movie->images()->create([
                    'backdrops' => $image,
                    'posters' => $image,
                ]);
            }
        }
    }

    private function handleRelations($data, $movie){
        $movie->actors()->detach();
        $movie->genreses()->detach();
        $tag_ids = array_map(function($item){
            return $item['id'];
        }, $data['tags']);
        $movie->actors()->attach(explode(',', $data['actor_ids']));
        $movie->genreses()->attach($tag_ids);
    }
}
