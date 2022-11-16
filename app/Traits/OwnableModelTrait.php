<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait OwnableModelTrait
{

  /**
   * Undocumented function
   *
   * @param User $user
   * @return boolean
   */
  public function isOwnedBy( User $user )
  {
    $modelQuery = $this->query();
    $userModels = $modelQuery->ownedBy( $user )->get('id');
    return $userModels->contains($this->id);
  }


  /**
   * Scope a query to only include models owned by a certain user.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @param  \App\Model\User  $type
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeOwnedBy($query, User $user)
  {
    $modelTableName = $this->getTable();
    $userModelIds = $user->$modelTableName->pluck('id');
    return $query->whereIn('id', $userModelIds);
  }

  /**
   * Get model direct children
   *
   * @param Model $model
   * @return collection
   */
  protected function getDirectChildren(Model $model)
  {
    return $model->children;
  }

  public function getHikingRoutes()
  {
    return collect( $this->getHikingRoutesByModel( $this ) );
  }

  /**
   * Iterate over all model children to get all tree children
   *
   * @param Model $model
   * @return collection
   */
  protected function getHikingRoutesByModel(Model $model)
  {
    if ( ! method_exists($model->children()->getRelated(), 'children') ) {//end in Sector model
      return $model->children->all();
    }
    else {
      $children = $model->children->all();

      $childrenOfChildren = [];
      foreach ( $children as $child )
      {
        $allChildren = $this->getHikingRoutesByModel( $child );
        if ( count($allChildren) > 0 )
          $childrenOfChildren += $allChildren;
      }

      return $childrenOfChildren ;
    }
  }
}
