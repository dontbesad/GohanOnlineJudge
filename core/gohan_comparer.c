#include <stdio.h>
#include <string.h>
#include <stdlib.h>

#define AC     1
#define WA     2
#define PE     3
#define OLE    4
#define SYSERR 5

#define LEN    250

typedef struct {
    char data_in_path[LEN];
    char data_out_path[LEN];
} gohan_config;

gohan_config g_config;

int gohan_compare();

int gohan_compare() {

    int flag = AC;

    FILE *fp_ans  = fopen(g_config.data_in_path,  "r");
    FILE *fp_user = fopen(g_config.data_out_path, "r");

    if (fp_ans == NULL || fp_user == NULL) {
        return SYSERR;
    }
    char s1, s2;
    while ((s1 = fgetc(fp_ans)) != EOF) {
        if ((s2 = fgetc(fp_user)) != EOF) {
            if (s1 != s2) {
                return WA;
            }
        } else {
            if (feof(fp_ans) && (s1 == '\n' || s1 == ' ')) {
                return PE;
            } else {
                return WA;
            }
        }
    }

    while ((s2 = fgetc(fp_user)) != EOF) {
        flag = PE;
        if (s2 != '\n' && s2 != ' ') {
            return OLE;
        }
    }

    return flag;
}
/**
 * ./comparer /home/yy/web/YOJ/data.out /home/yy/web/YOJ/user.out
 */
int main(int argc, char **argv)
{
    if (argc != 3) {
        exit(0);
    }

    strcpy(g_config.data_in_path,  argv[1]); //ans
    strcpy(g_config.data_out_path, argv[2]);

    int res = gohan_compare();
    printf("{\"code\":%d}", res);
    exit(res);
    return 0;
}
