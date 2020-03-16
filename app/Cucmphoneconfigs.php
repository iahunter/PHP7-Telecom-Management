<?php

namespace App;

//use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Cucmphoneconfigs extends Model
{
    //
    //use Auditable;
    use SoftDeletes;
    protected $table = 'cucmphone';
    protected $fillable = ['name', 'description', 'devicepool', 'css', 'model', 'ownerid', 'ipv4address', 'erl', 'risdb_ipv4address', 'risdb_registration_status', 'lines', 'config', 'last_registered'];

    // Cast data type conversions. Converting one type of data to another.
    protected $casts = [
            'lines'           => 'array',
            'config'          => 'array',
        ];

    protected static function boot()
    {
        parent::boot();
    }
	
	public static function get_count_phone_models_inuse()
    {
        $models = DB::table('cucmphone')
            ->select('cucmphone.model', DB::raw('count(cucmphone.model) as count'))
			->whereNull('deleted_at')
            ->groupBy('model')
            ->orderBy('model')
            ->get();

        return $models; 
    }
	
	public static function get_active_phone_count()
    {
        $count = Cucmphoneconfigs::all()->count();

        return $count; 
    }
	
	public static function get_phone_registered_count()
    {
		$count = Cucmphoneconfigs::where('risdb_registration_status', 'Registered')->count(); 
        
		return $count; 
    }
	
	public static function get_phone_registered_count_by_type()
    {
        $array = []; 
		
		$models = Cucmphoneconfigs::get_count_phone_models_inuse(); 
		
		foreach($models as $model){
			//print $model->model . PHP_EOL; 
			$count = Cucmphoneconfigs::where('model', $model->model)
										->where('risdb_registration_status', 'Registered')
										->count(); 
			$model->registered = $count; 
			
			$key = $model->model; 
			unset($model->model); 
			
			$array[$key] = $model; 
		}

        return $array; 
    }
	
}
