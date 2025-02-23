struct prepare_table
{
	char	name[100];
	char 	code[3];
};

struct prepare_list
{
	char	name[100];
	int 	id;
	int 	skip;
	int 	max;
};

struct prepare_subject
{
	char 	subject[320];
	int 	mm;
	int 	len;
};

struct prepare_extra
{
	char 	field[120];
	char 	mm_default[300];
	int 	len;
};

struct prepare_seed
{
	char 	email[500];
	int 	position;
	int 	position_random;
};

struct prepare
{
	struct			prepare_table tables[30];
	struct			prepare_list lists[50];
	struct			prepare_subject subjects[50];
	struct 			prepare_extra extra_fields[30];
	struct 			prepare_seed seeds[40];
	char 			sql_extra[2000];
	int 			table_count;
	int 			list_count;
	int 			subject_count;
	int 			subject_next;
	int 			extra_count;
	int 			seed_count;
	int 			msg_id;
	int 			id;
	int 			suppression_count;
	int 			has_md5;
	hash_table_t 	*hash_table;
	FILE 			*list;
};
