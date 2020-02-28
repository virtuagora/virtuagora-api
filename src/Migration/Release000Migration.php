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
            $t->json('localized_fields_schema');
            $t->json('extra_fields_schema');
            $t->string('parent_id')->nullable();
            $t->foreign('parent_id')->references('id')->on('place_types')->onDelete('set null');
            $t->timestamps();
        });
        $this->schema->create('places', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('code');
            $t->integer('size')->unsigned()->default(0);
            $t->json('extra_fields')->nullable();
            $t->integer('parent_id')->unsigned()->nullable();
            $t->foreign('parent_id')->references('id')->on('places')->onDelete('cascade');
            $t->integer('space_id')->unsigned()->nullable();
            $t->foreign('space_id')->references('id')->on('spaces')->onDelete('set null');
            $t->string('place_type_id');
            $t->foreign('place_type_id')->references('id')->on('place_types')->onDelete('restrict');
            $t->timestamps();
        });
        $this->schema->create('places_translations', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->integer('place_id')->unsigned();
            $t->string('locale')->index();
            $t->string('name');
            $t->json('localized_fields')->nullable();
            $t->string('trace')->nullable();
            $t->unique(['place_id', 'locale']);
            $t->foreign('place_id')->references('id')->on('places')->onDelete('cascade');
        });
        $this->schema->create('roles', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->string('id')->primary();
            $t->string('name')->unique();
            $t->boolean('show_badge');
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
            $t->boolean('individual'); // individual agents or collection?
            $t->json('allowed_relations')->nullable(); // list of allowed relations for every group
            $t->json('localized_fields_schema');
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
            $t->string('avatar_hash');
            $t->double('score')->default(0);
            $t->boolean('banned')->default(false);
            $t->string('locale')->default('en');
            $t->json('extra_fields')->nullable();
            $t->json('hidden_fields')->nullable();
            $t->json('pictures')->nullable();
            $t->integer('person_id')->unsigned()->nullable();
            $t->foreign('person_id')->references('id')->on('people')->onDelete('cascade');
            $t->integer('place_id')->unsigned()->nullable();
            $t->foreign('place_id')->references('id')->on('places')->onDelete('set null');
            $t->string('agent_type_id');
            $t->foreign('agent_type_id')->references('id')->on('agent_types')->onDelete('restrict');
            $t->timestamps();
        });
        $this->schema->create('agents_translations', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->integer('agent_id')->unsigned();
            $t->string('locale')->index();
            $t->text('description')->nullable();
            $t->json('localized_fields')->nullable();
            $t->string('trace')->nullable();
            $t->unique(['agent_id', 'locale']);
            $t->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
        });
        // $this->schema->create('credentials', function (Blueprint $t) {
        //     $t->engine = 'InnoDB';
        //     $t->increments('id');
        //     $t->string('username')->unique();
        //     $t->string('password')->nullable();
        //     $t->boolean('banned')->default(false);
        //     $t->string('email')->nullable();
        //     $t->string('facebook')->nullable();
        //     $t->string('phone')->nullable();
        //     $t->integer('agent_id')->unsigned();
        //     $t->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
        //     $t->index('email');
        //     $t->index('facebook');
        //     $t->index('phone');
        // });
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
            $t->string('finder')->nullable();
            $t->integer('agent_id')->unsigned()->nullable();
            $t->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $t->timestamp('expires_on')->nullable();
            $t->timestamps();
            $t->index('finder');
        });
        $this->schema->create('node_types', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->string('id')->primary();
            $t->string('name');
            $t->text('description');
            $t->json('node_relations')->nullable();
            $t->json('agent_relations')->nullable();
            $t->json('localized_fields_schema');
            $t->json('extra_fields_schema');
            $t->json('hidden_fields_schema');
            $t->timestamps();
        });
        $this->schema->create('nodes', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->double('score')->default(0);
            $t->dateTime('close_date')->nullable();
            $t->boolean('unlisted')->default(false);
            $t->json('extra_fields')->nullable();
            $t->json('hidden_fields')->nullable();
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
        $this->schema->create('nodes_translations', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->integer('node_id')->unsigned();
            $t->string('locale')->index();
            $t->string('title');
            $t->text('content')->nullable();
            $t->json('localized_fields')->nullable();
            $t->string('trace')->nullable();
            $t->unique(['node_id', 'locale']);
            $t->foreign('node_id')->references('id')->on('nodes')->onDelete('cascade');
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
            $t->json('localized_fields_schema');
            $t->json('extra_fields_schema');
            $t->json('rules');
            $t->timestamps();
        });
        $this->schema->create('terms', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->integer('size')->unsigned()->default(0);
            $t->json('extra_fields')->nullable();
            $t->string('term_type_id');
            $t->foreign('term_type_id')->references('id')->on('term_types')->onDelete('cascade');
            $t->timestamps();
        });
        $this->schema->create('terms_translations', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->integer('term_id')->unsigned();
            $t->string('locale')->index();
            $t->string('name');
            $t->json('localized_fields')->nullable();
            $t->string('trace')->nullable();
            $t->unique(['term_id', 'locale']);
            $t->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
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
            $t->string('first_allowed_relations');
            $t->string('second_allowed_relations');
            $t->timestamps();
        });
        $this->schema->create('logs', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('action_id');
            $t->integer('agent_id')->unsigned()->nullable();
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
                'id' => 'StaffGroup',
                'name' => 'Staff group',
                'show_badge' => false,
            ], [
                'id' => 'InitiativeGroup',
                'name' => 'Initiative group',
                'show_badge' => false,
            ],
        ]);

        $this->db->createAndSave('App:GroupType', [
            'id' => 'Staff',
            'name' => 'Staff',
            'description' => 'Administration teams',
            'role_id' => 'StaffGroup',
            'public_schema' => [
                'type' => 'null',
            ],
            'private_schema' => [
                'type' => 'null',
            ],
        ]);
        $this->db->createAndSave('App:GroupType', [
            'id' => 'Initiative',
            'name' => 'Initiative',
            'description' => 'Youth initiatives',
            'role_id' => 'InitiativeGroup',
            'allowed_relations' => [
                'owner' => [
                    'name' => 'Owner',
                ],
            ],
            'public_schema' => [
                'type' => 'object',
                'properties' => [
                    'founding_year' => [
                        'type' => 'integer',
                        'minimum' => 1,
                        'maximum' => 2020,
                    ],
                    'goals' => [
                        'type' => 'string',
                        'minLength' => 1,
                        'maxLength' => 750,
                    ],
                    'website' => [
                        'type' => 'string',
                        'minLength' => 10,
                        'maxLength' => 100,
                    ],
                    'facebook' => [
                        'type' => 'string',
                        'minLength' => 10,
                        'maxLength' => 100,
                    ],
                    'twitter' => [
                        'type' => 'string',
                        'minLength' => 10,
                        'maxLength' => 100,
                    ],
                    'other_network' => [
                        'type' => 'string',
                        'minLength' => 10,
                        'maxLength' => 100,
                    ],
                    'role_of_youth' => [
                        'type' => 'string',
                        'enum' => [
                            'targetAudience', 'leadership', 'membership'
                        ],
                    ],
                    'interested_in_participate' => [
                        'type' => 'boolean',
                        'default' => false,
                    ],
                ],
                'required' => [
                    'founding_year', 'goals', 'role_of_youth',
                ],
                'additionalProperties' => false,
            ],
            'private_schema' => [
                'type' => 'object',
                'properties' => [
                    'contact_email' => [
                        'type' => 'string',
                        'minLength' => 5,
                        'maxLength' => 100,
                        'format' => 'email',
                    ],
                    'contact_phone' => [
                        'type' => 'string',
                        'minLength' => 5,
                        'maxLength' => 20,
                    ],
                ],
                'required' => [
                    'contact_email',
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->db->createAndSave('App:Taxonomy', [
            'id' => 'topics',
            'name' => 'Topics',
            'description' => 'ICT and Internet related topics which are of interest or concern',
            'rules' => [
                'habtm' => true, // HasAndBelongsToMany
            ],
            'schema' => [
                'type' => 'null',
            ],
        ]);

        $this->db->table('terms')->insert([
            ['name' => 'Access for people with disabilities', 'trace' => 'Accessforpeoplewithdisabilities', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Access to Information', 'trace' => 'AccesstoInformation', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Cybersecurity and Data Protection', 'trace' => 'CybersecurityandDataProtection', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'LGBT+ Community', 'trace' => 'LGBTCommunity', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Entrepreneurship', 'trace' => 'Entrepreneurship', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Arts and Culture', 'trace' => 'ArtsandCulture', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Democracy', 'trace' => 'Democracy', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Human Rights', 'trace' => 'HumanRights', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Gender Equality', 'trace' => 'GenderEquality', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Social Inclusion and Inequality', 'trace' => 'SocialInclusionandInequality', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Technological Innovation', 'trace' => 'TechnologicalInnovation', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Internet of Things', 'trace' => 'InternetofThings', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Freedom of Expression', 'trace' => 'FreedomofExpression', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Religious Freedom', 'trace' => 'ReligiousFreedom', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Fight Against Racism', 'trace' => 'FightAgainstRacism', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Net Neutrality', 'trace' => 'NetNeutrality', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Academic Research', 'trace' => 'AcademicResearch', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Community Networks', 'trace' => 'CommunityNetworks', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Health', 'trace' => 'Health', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Transparency and Government Accountability', 'trace' => 'TransparencyandGovernmentAccountability', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
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
