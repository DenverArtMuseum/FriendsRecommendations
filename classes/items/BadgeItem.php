<?php namespace DMA\Recommendations\Classes\Items;

use Log;
use DMA\Recommendations\Classes\Items\ItemBase;
use Carbon\Carbon;

/**
 * Badge Item 
 * @author Carlos Arroyo
 *
 */
class BadgeItem extends ItemBase
{
    
    /**
     * {@inheritDoc}
     * @return array
     */
    public function getDetails()
    {
        return [
			'name' => 'Badges',
			'description' => 'Recommend badges base on tags and user activity.'
        ];
    }    
    
    /**
     * {@inheritDoc}
     * @return string
     */
    public function getKey()
	{
		return 'badge';
	}

	/**
	 * {@inheritDoc}
	 * @return string
	 */
	public function getModel()
	{
	    return '\DMA\Friends\Models\Badge';
	}

	/**
	 * {@inheritDoc}
	 * @return QueryBuilder
	 */
	public function getQueryScope()
	{
		return parent::getQueryScope()->isActive();
	}	
	
	/**
	 * {@inheritDoc}
	 * @see \DMA\Recommendations\Classes\Items\ItemBase::addSettingsFields()
	 */
	public function getSettingsFields()
	{
		return [];
	}
	
	/**
	 * {@inheritDoc}
	 * @see \DMA\Recommendations\Classes\Items\ItemBase::addFeatures()
	 */
	public function getFeatures()
	{
	    return [
	        'users',	        
            'categories',
	    ];
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Recommendations\Classes\Items\ItemBase::addFilters()
	 */
	public function getFilters()
	{
		return [
		  ['time_restrictions', 'type' => 'object']
        ];
	}	
  
	/**
	 * {@inheritDoc}
	 * @see \DMA\Recommendations\Classes\Items\ItemBase::addWeightFeatures()
	 */
	public function getWeightFeatures()
	{
		return [
		  ['priority',  'type' => 'integer']
		];
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Recommendations\Classes\Items\ItemBase::getItemRelations()
	 */
	public function getItemRelations()
	{
	    return [
	       'users' => '\DMA\Recommendations\Classes\Items\UserItem',
	    ];
	}	

	/**
	 * {@inheritDoc}
	 * @see \DMA\Recommendations\Classes\Items\ItemBase::getUpdateAtEvents()
	 */
	public function getUpdateEvents()
	{
	    $k = $this->getModel();
	    $k = (substr( $k, 0, 1 ) === "\\") ? substr($k, 1, strlen($k)) : $k;
	    return [
		  'dma.friends.badge.completed',
		  'eloquent.created: ' . $k,
		  'eloquent.updated: ' . $k
		];		
	}	
		
	
	// PREPARE DATA METHODS
	public function getCategories($model){
	
		$clean = [];
		$model->categories->each(function($r) use (&$clean){
			//$clean[] = [ 'id' => $r->getKey(), 'name' => $r->name ];
			$clean[] = $r->name;
		});
		return $clean;
	
	}	
	
	public function getTimeRestrictions($model){
	
	    $restrictions = [];
	    $keys         = ['date_begin', 'date_end'];
	
	    // Reset values
	    foreach($keys as $key){
	        $restrictions[$key] = null;
	    }
	
        $restrictions['date_begin'] = $this->carbonToIso($model->date_begin);
        $restrictions['date_end']   = $this->carbonToIso($model->date_end);
	
	    return $restrictions;
	}
	
	protected function carbonToIso($carbonDate, $bit=null)
	{
	    if(!is_null($carbonDate)){
	        if (is_null($bit)){
	        	   return $carbonDate->toIso8601String();
	        }else if ($bit == 'date'){
	            return $carbonDate->toDateString();
	        }else if ($bit == 'time'){
	            return $carbonDate->toTimeString();
	        }
	    }
	}
	

	#########
	# Filters
	#########
	
	public function filterTimeRestrictions($backend)
	{
	$today = new Carbon();
	
	// Split date and time
	$date = $today->toDateString();
	$time = $today->toTimeString();
	
	// Filters using SOLR syntax. ElasticSearch DSL can
	// be used if the return value is an Array
	
    $filter = "( time_restrictions.date_begin:[ * TO now ] AND
	time_restrictions.date_end:[ now TO * ] )";
	
	// Clean up string
	$filter = str_replace(["\n","\r"], '', $filter);
	$filter = $this->normalizeWhiteSpace($filter);
	return $filter;
	
	
	}	
}
