<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\NaviNode;
use Illuminate\Database\Seeder;
class naviNodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Collection $collection)
    {
        //

        // $collection->storeCollectionData('testbbb2aa2', 'url-abbsbbs22', 'aaaa', 'bbbbb', 'y', []);

        $re = NaviNode::createNode(0, 'level1-a', null, null, 1);

        if ($re['success']) {
            NaviNode::createNode($re['id'], 'level2-a', null, 1, 0);
            $reb = NaviNode::createNode($re['id'], 'level2-b', null, null, 1);

            if ($reb['success']) {
                NaviNode::createNode($reb['id'], 'level3-a');
                NaviNode::createNode($reb['id'], 'level3-b');
                NaviNode::createNode($reb['id'], 'level3-c');
            }
        }

        $re = NaviNode::createNode(0, 'level1-b', 'aaa', 1);



    }
}
