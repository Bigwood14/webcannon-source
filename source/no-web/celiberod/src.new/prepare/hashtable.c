#include <stdio.h>
#include <stdlib.h>
#include <math.h>
#include <string.h>

#include "hashtable.h"

hash_table_t *create_hash_table(int size)
{
    hash_table_t *new_table;
	int i;   
 
    if (size<1) return NULL; /* invalid size for table */

    /* Attempt to allocate memory for the table structure */
    if ((new_table = malloc(sizeof(hash_table_t))) == NULL) {
        return NULL;
    }
    
    /* Attempt to allocate memory for the table itself */
    if ((new_table->table = malloc(sizeof(list_t *) * size)) == NULL) {
        return NULL;
    }

    /* Initialize the elements of the table */
    for(i=0; i<size; i++) new_table->table[i] = NULL;

    /* Set the table's size */
    new_table->size = size;

    return new_table;
}


unsigned int hash(hash_table_t *hashtable, char *str)
{
    unsigned int hashval;
    
    /* we start our hash out at 0 */
    hashval = 0;

    /* for each character, we multiply the old hash by 31 and add the current
 *      * character.  Remember that shifting a number left is equivalent to 
 *           * multiplying it by 2 raised to the number of places shifted.  So we 
 *                * are in effect multiplying hashval by 32 and then subtracting hashval.  
 *                     * Why do we do this?  Because shifting and subtraction are much more 
 *                          * efficient operations than multiplication.
 *                               */
    for(; *str != '\0'; str++) hashval = *str + (hashval << 5) - hashval;

    /* we then return the hash value mod the hashtable size so that it will
 *      * fit into the necessary range
 *           */
    return hashval % hashtable->size;
}


list_t *lookup_string(hash_table_t *hashtable, char *str)
{
    list_t *list;
    unsigned int hashval = hash(hashtable, str);

    /* Go to the correct list based on the hash value and see if str is
 *      * in the list.  If it is, return return a pointer to the list element.
 *           * If it isn't, the item isn't in the table, so return NULL.
 *                */
    for(list = hashtable->table[hashval]; list != NULL; list = list->next) {
        if (strcmp(str, list->string) == 0) return list;
    }
    return NULL;
}

int lookup_str(hash_table_t *hashtable, char *str)
{
    list_t *list;
    unsigned int hashval = hash(hashtable, str);

    /* Go to the correct list based on the hash value and see if str is
 *      * in the list.  If it is, return return a pointer to the list element.
 *           * If it isn't, the item isn't in the table, so return NULL.
 *                */
    for(list = hashtable->table[hashval]; list != NULL; list = list->next) {
        if (strcmp(str, list->string) == 0) return 1;
    }
    return -1;
}



int add_string(hash_table_t *hashtable, char *str)
{
    list_t *new_list;
    list_t *current_list;
    unsigned int hashval = hash(hashtable, str);

    /* Attempt to allocate memory for list */
    if ((new_list = malloc(sizeof(list_t))) == NULL) return 1;

    /* Does item already exist? */
    current_list = lookup_string(hashtable, str);
        /* item already exists, don't insert it again. */
    if (current_list != NULL) return 2;
    /* Insert into list */
    new_list->string = strdup(str);
    new_list->next = hashtable->table[hashval];
    hashtable->table[hashval] = new_list;

    return 0;
}

void free_table(hash_table_t *hashtable)
{
    int i;
    list_t *list, *temp;

    if (hashtable==NULL) return;

    /* Free the memory for every item in the table, including the 
 *      * strings themselves.
 *           */
    for(i=0; i<hashtable->size; i++) {
        list = hashtable->table[i];
        while(list!=NULL) {
            temp = list;
            list = list->next;
            free(temp->string);
            free(temp);
        }
    }

    /* Free the table itself */
    free(hashtable->table);
    free(hashtable);
}

/*void main ()
{
	hash_table_t *my_hash_table;
	int size_of_table = 12;
	int ret;	

	my_hash_table = create_hash_table(size_of_table);
	add_string(my_hash_table, "tom");
	
	ret = lookup_str(my_hash_table, "tom");
	if (ret == 1)
		printf("found tom\n");

	ret = lookup_str(my_hash_table, "yahoo.com");
	if (ret == 1)
		printf("found yahoo.com\n");

	add_string(my_hash_table, "yahoo.com");
	ret = lookup_str(my_hash_table, "yahoo.com");
	if (ret == 1)
		printf("found yahoo.com\n");
}*/
