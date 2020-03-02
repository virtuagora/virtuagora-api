<?php

namespace App\Migration;

use Grimzy\LaravelMysqlSpatial\Schema\Blueprint;

// timestamps: !relation && !weakEntity

class Release000Migration
{
    protected $db;
    protected $schema;
    
    public function __construct($db)
    {
        $this->db = $db;
        $this->schema = $db->schema();
        $this->schema->blueprintResolver(function($t, $callback) {
            return new Blueprint($t, $callback);
        });
    }

    public function isInstalled()
    {
        return $this->schema->hasTable('options');
    }

    public function up()
    {
        $this->schema->create('options', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('key')->unique();
            $t->text('value')->nullable();
            $t->string('type'); //integer, string, text, hidden
            $t->string('group');
            $t->boolean('autoload')->default(false);
            $t->timestamps();
        });
        $this->schema->create('seasons', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('name');
            $t->string('code');
            $t->integer('size')->unsigned()->default(0);
            $t->json('extra_fields')->nullable();
            $t->timestamps();
        });
        $this->schema->create('spaces', function (Blueprint $t) {
            $t->increments('id');
            $t->point('point');
            $t->lineString('line_string')->nullable();
            $t->polygon('polygon')->nullable();
            $t->multiPoint('multi_point')->nullable();
            $t->multiLineString('multi_line_string')->nullable();
            $t->multiPolygon('multi_polygon')->nullable();
            $t->string('type')->default('Point');
            $t->spatialIndex('point');
            $t->timestamps();
        });
        $this->schema->create('place_types', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->string('id')->primary();
            $t->string('name');
            $t->json('extra_fields_schema');
            $t->string('parent_id')->nullable();
            $t->foreign('parent_id')->references('id')->on('place_types')->onDelete('set null');
            $t->timestamps();
        });
        $this->schema->create('places', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('code');
            $t->string('name'); // T
            $t->integer('size')->unsigned()->default(0);
            $t->json('extra_fields')->nullable();
            $t->string('trace')->nullable(); // T
            $t->integer('parent_id')->unsigned()->nullable();
            $t->foreign('parent_id')->references('id')->on('places')->onDelete('cascade');
            $t->integer('space_id')->unsigned()->nullable();
            $t->foreign('space_id')->references('id')->on('spaces')->onDelete('set null');
            $t->string('place_type_id');
            $t->foreign('place_type_id')->references('id')->on('place_types')->onDelete('restrict');
            $t->timestamps();
        });
        $this->schema->create('roles', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->string('id')->primary();
            $t->string('name')->unique();
            $t->text('description')->nullable();
            $t->boolean('show_badge')->default(false);
            $t->string('icon')->nullable();
            $t->json('extra_fields')->nullable();
            $t->timestamps();
        });
        $this->schema->create('people', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('names');
            $t->string('surnames');
            $t->string('person_id')->nullable();
            $t->string('person_id_type')->nullable();
            $t->string('gender')->nullable();
            $t->timestamps();
        });
        $this->schema->create('agent_types', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->string('id')->primary();
            $t->text('description')->nullable();
            $t->boolean('individual'); // individual agents or collection?
            $t->json('allowed_relations')->nullable(); // list of allowed relations for every group
            $t->json('extra_fields_schema');
            $t->json('hidden_fields_schema');
            $t->string('role_id')->nullable(); // role for the agents of this type
            $t->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $t->timestamps();
        });
        $this->schema->create('agents', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('display_name');
            $t->text('description')->nullable(); // T
            $t->string('avatar');
            $t->double('score')->default(0);
            $t->boolean('banned')->default(false);
            $t->string('locale')->default('en');
            $t->json('extra_fields')->nullable();
            $t->json('hidden_fields')->nullable();
            $t->json('pictures')->nullable();
            $t->string('trace')->nullable(); // T
            $t->integer('person_id')->unsigned()->nullable();
            $t->foreign('person_id')->references('id')->on('people')->onDelete('cascade');
            $t->integer('place_id')->unsigned()->nullable();
            $t->foreign('place_id')->references('id')->on('places')->onDelete('set null');
            $t->string('agent_type_id');
            $t->foreign('agent_type_id')->references('id')->on('agent_types')->onDelete('restrict');
            $t->timestamps();
        });
        $this->schema->create('account_types', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->string('id')->primary();
            $t->string('name');
            $t->string('type'); // ['local', 'oauth2']
            $t->json('settings')->nullable();
            $t->timestamps();
        });
        $this->schema->create('accounts', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('username')->unique();
            $t->string('secret')->nullable();
            $t->json('extra_fields')->nullable();
            $t->boolean('public')->default(false);
            $t->integer('agent_id')->unsigned();
            $t->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $t->string('account_type_id');
            $t->foreign('account_type_id')->references('id')->on('account_types')->onDelete('cascade');
            $t->timestamps();
            $t->index('username');
        });
        $this->schema->create('agent_agent', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('relation');
            $t->integer('parent_id')->unsigned();
            $t->foreign('parent_id')->references('id')->on('agents')->onDelete('cascade');
            $t->integer('child_id')->unsigned();
            $t->foreign('child_id')->references('id')->on('agents')->onDelete('cascade');
        });
        $this->schema->create('agent_role', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->integer('agent_id')->unsigned();
            $t->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $t->string('role_id');
            $t->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
        $this->schema->create('tokens', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('token')->unique();
            $t->string('type');
            $t->json('data')->nullable();
            $t->integer('agent_id')->unsigned()->nullable();
            $t->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $t->timestamp('expires_on')->nullable();
            $t->timestamps();
        });
        $this->schema->create('node_types', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->string('id')->primary();
            $t->string('name');
            $t->text('description');
            $t->json('node_relations')->nullable();
            $t->json('agent_relations')->nullable();
            $t->json('extra_fields_schema');
            $t->json('hidden_fields_schema');
            $t->timestamps();
        });
        $this->schema->create('nodes', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('title'); // T
            $t->text('content')->nullable(); // T
            $t->double('score')->default(0);
            $t->dateTime('close_date')->nullable();
            $t->boolean('unlisted')->default(false);
            $t->json('extra_fields')->nullable();
            $t->json('hidden_fields')->nullable();
            $t->string('trace')->nullable(); // T
            $t->integer('maker_id')->unsigned();
            $t->foreign('maker_id')->references('id')->on('agents')->onDelete('cascade');
            $t->integer('space_id')->unsigned()->nullable();
            $t->foreign('space_id')->references('id')->on('spaces')->onDelete('set null');
            $t->integer('place_id')->unsigned()->nullable();
            $t->foreign('place_id')->references('id')->on('places')->onDelete('set null');
            $t->string('node_type_id');
            $t->foreign('node_type_id')->references('id')->on('node_types')->onDelete('restrict');
            $t->timestamps();
        });
        $this->schema->create('node_node', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('relation')->nullable();
            $t->integer('parent_id')->unsigned();
            $t->foreign('parent_id')->references('id')->on('nodes')->onDelete('cascade');
            $t->integer('child_id')->unsigned();
            $t->foreign('child_id')->references('id')->on('nodes')->onDelete('cascade');
        });
        $this->schema->create('node_agent', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('relation');
            $t->integer('value')->nullable();
            $t->integer('node_id')->unsigned();
            $t->foreign('node_id')->references('id')->on('nodes')->onDelete('cascade');
            $t->integer('agent_id')->unsigned();
            $t->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
        });
        $this->schema->create('comments', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->text('content');
            $t->double('score')->default(0);
            $t->json('extra_fields')->nullable();
            $t->integer('node_id')->unsigned();
            $t->foreign('node_id')->references('id')->on('nodes')->onDelete('cascade');
            $t->integer('maker_id')->unsigned();
            $t->foreign('maker_id')->references('id')->on('agents')->onDelete('cascade');
            $t->integer('parent_id')->unsigned()->nullable();
            $t->foreign('parent_id')->references('id')->on('comments')->onDelete('cascade');
            $t->timestamps();
        });
        $this->schema->create('comment_votes', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->integer('value');
            $t->integer('agent_id')->unsigned();
            $t->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $t->integer('comment_id')->unsigned();
            $t->foreign('comment_id')->references('id')->on('comments')->onDelete('cascade');
            $t->timestamps();
        });
        $this->schema->create('term_types', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->string('id')->primary();;
            $t->string('name');
            $t->text('description');
            $t->json('extra_fields_schema');
            $t->json('rules');
            $t->timestamps();
        });
        $this->schema->create('terms', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('name'); // T
            $t->integer('size')->unsigned()->default(0);
            $t->json('extra_fields')->nullable();
            $t->string('trace')->nullable(); // T
            $t->string('term_type_id');
            $t->foreign('term_type_id')->references('id')->on('term_types')->onDelete('cascade');
            $t->timestamps();
        });
        $this->schema->create('term_object', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->integer('term_id')->unsigned();
            $t->string('object_type');
            $t->integer('object_id')->unsigned();
            $t->json('data')->nullable();
            $t->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $t->index(['object_type', 'object_id']);
            $t->timestamps();
        });
        $this->schema->create('actions', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->string('id')->primary();
            $t->string('group');
            $t->string('rule'); // anyOf or allOf
            $t->string('allowed_roles');
            $t->string('allowed_first_targets');
            $t->string('allowed_second_targets');
            $t->timestamps();
        });
        $this->schema->create('logs', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('action_id');
            $t->integer('agent_id')->unsigned()->nullable();
            $t->integer('proxy_id')->unsigned()->nullable();
            $t->string('first_target_type');
            $t->integer('first_target_id')->unsigned();
            $t->string('second_target_type')->nullable();
            $t->integer('second_target_id')->unsigned()->nullable();
            $t->json('parameters')->nullable();
            $t->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $t->foreign('action_id')->references('id')->on('actions')->onDelete('cascade');
            $t->index(['first_target_type', 'first_target_id']);
            $t->index(['second_target_type', 'second_target_id']);
            $t->timestamps();
        });

        // --- Plugin content ballots ---

        $this->schema->create('ballots', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->json('options');
            $t->integer('total_votes')->unsigned()->default(0);
            $t->boolean('secret')->default(false);
            $t->integer('node_id')->unsigned();
            $t->foreign('node_id')->references('id')->on('nodes')->onDelete('cascade');
        });
    }

    public function down()
    {
        $this->schema->dropAllTables();
    }

    public function populate()
    {
        $today = \Carbon\Carbon::now();

        $this->db->table('roles')->insert([
            [
                'id' => 'User',
                'name' => 'User',
                'show_badge' => false,
            ], [
                'id' => 'Verified',
                'name' => 'Verified user',
                'show_badge' => true,
            ], [
                'id' => 'Admin',
                'name' => 'Admnistrator',
                'show_badge' => true,
            ], [
                'id' => 'Mod',
                'name' => 'Moderator',
                'show_badge' => true,
            ], [
                'id' => 'StaffGroup',
                'name' => 'Staff group',
                'show_badge' => false,
            ],
        ]);

        $this->db->createAndSave('App:AgentType', [
            'id' => 'User',
            'individual' => true,
            'role_id' => 'User',
            'extra_fields_schema' => [
                'type' => 'null',
            ],
            'hidden_fields_schema' => [
                'type' => 'null',
            ],
        ]);

        $this->db->createAndSave('App:TermType', [
            'id' => 'category',
            'name' => 'Categories',
            'description' => 'Generic categories',
            'rules' => [
                'habtm' => true, // HasAndBelongsToMany
            ],
            'extra_fields_schema' => [
                'type' => 'null',
            ],
        ]);
    }

    public function updateActions()
    {
        $this->db->table('actions')->insert([
            ['id' => 'updateUserPassword', 'group' => 'user', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '["self"]', 'allowed_proxies' => '[]'],
            ['id' => 'createInitiative', 'group' => 'initiative', 'allowed_roles' => '["User"]', 'allowed_relations' => '[]', 'allowed_proxies' => '[]'],
            ['id' => 'createRegisteredCity', 'group' => 'initiative', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '[]', 'allowed_proxies' => '[]'],
            ['id' => 'createTerm', 'group' => 'initiative', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '[]', 'allowed_proxies' => '[]'],
            ['id' => 'associateInitiativeTerm', 'group' => 'initiative', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '["owner"]', 'allowed_proxies' => '[]'],
            ['id' => 'deleteInitiative', 'group' => 'initiative', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '["owner"]', 'allowed_proxies' => '[]'],
            ['id' => 'associateSubjectRole', 'group' => 'user', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '[]', 'allowed_proxies' => '[]'],
        ]);
    }
}
