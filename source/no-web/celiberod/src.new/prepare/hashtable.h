typedef struct _list_t_ {
    char *string;
    struct _list_t_ *next;
} list_t;


typedef struct _hash_table_t_ {
    int size;       /* the size of the table */
    list_t **table; /* the table elements */
} hash_table_t;



hash_table_t *create_hash_table(int size);
int add_string(hash_table_t *hashtable, char *str);
int lookup_str(hash_table_t *hashtable, char *str);
