#include <stdio.h>
#include <unistd.h>
#include <stdlib.h>
#include <string.h>
#include <sys/stat.h>
#include <sys/wait.h>
#include <mysql/mysql.h> /* installed libmysqlclient-dev */
#include <pthread.h>
/* gcc -o gohan gohan.c -lmysqlclient -lpthread */

#define LEN 256

typedef struct {
    char workdir[LEN];

    char db_name[LEN];
    char db_user[LEN];
    char db_pass[LEN];
    char db_host[LEN];

    char compiler[LEN];
    char judger[LEN];
    char comparer[LEN];

    int max_thread;
} gohan_config;

gohan_config g_config;

int pthread_used[LEN];
int pthread_amount = 0;

pthread_mutex_t mutex = PTHREAD_MUTEX_INITIALIZER; //线程锁

void log_msg();
void gohan_init();
void read_config_file(char *, char *);
int  parse_json(char *, char *);
int  query_db(char *, int);
int  update_db(char *);
void pthread_func(int);
void gohan();
void get_judge_result(char *, char *);
void gohan_core(int, int, int, int, int);

void log_msg(char error[128], char msg[1024]) {
    //记录信息
}

void gohan_init() {

    read_config_file("OJ_WORKDIR=", g_config.workdir);

    read_config_file("OJ_COMPILER=", g_config.compiler);

    read_config_file("OJ_JUDGER=",    g_config.judger);

    read_config_file("OJ_COMPARER=",  g_config.comparer);

    read_config_file("OJ_DB_HOST=",  g_config.db_host);

    read_config_file("OJ_DB_NAME=",  g_config.db_name);

    read_config_file("OJ_DB_USER=",  g_config.db_user);

    read_config_file("OJ_DB_PASS=",  g_config.db_pass);

    g_config.max_thread = 0;
    char max_thread_str[10];
    read_config_file("OJ_THREAD=",   max_thread_str);
    int i, len = strlen(max_thread_str);
    for (i = 0; i < len; ++i) {
        g_config.max_thread = g_config.max_thread * 10 + max_thread_str[i] - '0';
    }

}
/**
 * 读取配置文件
 */
void read_config_file(char match[128], char res[128]) {

    strcpy(res, "\0");

    FILE *fp = fopen("gohan.conf", "r");

    if (fp == NULL) {
        return ;
    }

    char str[256];
    int len = strlen(match);

    while (fgets(str, 256, fp) != NULL) {

        char *p = strstr(str, match);
        if (p != NULL) {
            p[(int)strlen(p) - 1] = '\0';
            strcpy(res, p + len);
            break;
        }
    }
    fclose(fp);
}

/**
 * 解析字符串
 * @return        -1:syserr
 */
int parse_json(char buf[1024], char match[256]) {
    char *p = strstr(buf, match);
    if (p == NULL) {
        return -1;
    } else {
        int sum = 0;
        p = p + (int)strlen(match);
        while (*p != ',' && *p != '}') {
            sum = sum * 10 + *p - '0';
            p++;
        }
        return sum;
    }
}

/**
 * 获取数据库中某个字段(转化为数字)
 * @return        id
 */
int query_db(char sql[1024], int index) {
    MYSQL      mysql_conn;
    MYSQL_RES  *mysql_result;
    MYSQL_ROW  mysql_row;

    if (mysql_init(&mysql_conn) == NULL) {
        log_msg("error", "mysql init error");
        return 0;
    }

    if (mysql_real_connect(&mysql_conn, g_config.db_host, g_config.db_user, g_config.db_pass, g_config.db_name, 0, NULL, 0) == NULL) {
        log_msg("error", "mysql connect error");
        return 0;
    }

    int res = mysql_query(&mysql_conn, sql);
    if (res) {
        return 0;
    }

    mysql_result = mysql_store_result(&mysql_conn);
    if (mysql_result == NULL) {
        return 0;
    }

    if (mysql_row = mysql_fetch_row(mysql_result)) {
        mysql_free_result(mysql_result);
        int len = strlen(mysql_row[index]), sum = 0, i;
        for (i = 0; i < len; ++i) {
            sum = sum * 10 + mysql_row[index][i] - '0';
        }
        return sum;
    }
    return 0;
}

/**
 * 更新数据库中提交表的状态
 * @return    update amount
 */
int update_db(char sql[1024]) {
    MYSQL      mysql_conn;

    if (mysql_init(&mysql_conn) == NULL) {
        log_msg("error", "mysql init error");
        return 0;
    }

    if (mysql_real_connect(&mysql_conn, g_config.db_host, g_config.db_user, g_config.db_pass, g_config.db_name, 0, NULL, 0) == NULL) {
        log_msg("error", "mysql connect error");
        return 0;
    }

    int res = mysql_query(&mysql_conn, sql);
    if (res) {
        return 0;
    }
    return mysql_affected_rows(&mysql_conn);
}

/**
 * 线程逻辑
 */
void pthread_func(int id) {

    char query_sql[] = "SELECT `id` FROM `solutions` WHERE `result` = 'In Queue';";
    //char update_sql[] = "UPDATE `solutions` SET `result` = 'AC' WHERE `id` = 1;";
    int solution_id;
    char update_sql[1024];

    for ( ; ; ) {

        usleep(300000);
        pthread_mutex_lock(&mutex);
        usleep(300000);

        if (solution_id = query_db(query_sql, 0)) {

            sprintf(update_sql, "UPDATE `solutions` SET `result` = 'Compiling' WHERE `id` = %d;", solution_id);
            if (!update_db(update_sql)) {
                sprintf(update_sql, "UPDATE `solutions` SET `result` = 'In Queue' WHERE `id` = %d;", solution_id);
                pthread_mutex_unlock(&mutex);
                continue;
            }
            pthread_mutex_unlock(&mutex);

            gohan_core(id, 1000, 1024, 1000, solution_id);

            break;
        }

        pthread_mutex_unlock(&mutex);
    }

    pthread_used[id] = 2; //待释放
    pthread_exit(NULL);
}


void gohan() {

    pthread_t thrd[LEN];
    int status;
    memset(pthread_used, 0, sizeof(pthread_used));

    for ( ; ; ) {

        usleep(200000);
        //释放
        if (pthread_amount >= g_config.max_thread) {
            //这里有个神奇的现象？用for循环会循环双倍！
            int loop = g_config.max_thread;
            while (loop) {
                int i = loop--;
                if (pthread_used[i] == 2) {
                    printf("release pthread %d\n", i);
                    pthread_join(thrd[i], (void *)&status);
                    pthread_used[i] = 0;
                    pthread_amount--;
                    continue;
                }
            }

        }
        //创建
        int j;
        for (j = 1; j <= g_config.max_thread; ++j) {
            if (!pthread_used[j]) {
                printf("create pthread %d\n", j);
                long tmp = j;
                if (pthread_create(&thrd[j], NULL, (void *)pthread_func, (void *)tmp) != 0) {
                    continue;
                }
                pthread_amount++;
                pthread_used[j] = 1;
                break;
            }

        }

    }

}

void gohan_core(int runid, int runtime, int memory, int problem_id, int solution_id) {

    char compiler_cmd[LEN], judger_cmd[LEN], comparer_cmd[LEN];
    char runpath[LEN];
    char sourcepath[LEN];
    char rm_cmd[LEN] = "ls"; //删除runx目录下的所有文件
    char exec[LEN]   = "Main";
    char source[LEN] = "Main.cpp";
    char user_data[LEN] = "user.out";
    int compile_time = 2000; //编译时间MS

    sprintf(runpath, "%s/run%d", g_config.workdir, runid);
    sprintf(sourcepath, "%s/%s", runpath, source);

    /*if (strstr(runpath, "home") != NULL) {
        sprintf(rm_cmd, "rm -rf %s/*", runpath);
    }*/

    if (access(runpath, F_OK) != 0) {
        mkdir(runpath, 0775);

        FILE *fp = fopen(sourcepath, "w");
        fprintf(fp, "#include <stdio.h>\n int main() {int a, b; while (~scanf(\"%%d %%d\", &a, &b)) {printf(\"%%d\\n\", a + b);}}");
        fclose(fp);
    } else if (access(sourcepath, F_OK) != 0) {
        FILE *fp = fopen("Main.cpp", "w");
        if (fp == NULL) {
            printf("asdashdjashdasjkdhkjashdka\n");
        }
        fprintf(fp, "#include <stdio.h>\n int main() {int a, b; while (~scanf(\"%%d %%d\", &a, &b)) {printf(\"%%d\\n\", a + b);}}");
        fclose(fp);
    }

    sprintf(compiler_cmd, "%s %d %s/%s \"g++ %s/%s -o %s/%s 2> %s/ce.txt\"",
        g_config.compiler, compile_time, runpath, exec, runpath, source, runpath, exec, runpath);

    sprintf(judger_cmd, "%s %d %d %s/%s %s/data/%d/data.in %s/%s",
        g_config.judger, runtime, memory, runpath, exec, g_config.workdir, problem_id, runpath, user_data);

    sprintf(comparer_cmd, "%s %s/data/%d/data.out %s/%s",
        g_config.comparer, g_config.workdir, problem_id, runpath, user_data);
    //printf("%s\n%s\n%s\n", compiler_cmd, judger_cmd, comparer_cmd);

    char update_sql[LEN];
    char str[LEN];
    int code;
    //get_judge_result("./compiler 3000 1000.out \"g++ 1000.cpp -o 1000.out 2> CE.txt\"", str);
    get_judge_result(compiler_cmd, str);
    code = parse_json(str, "\"code\":");
    printf("res1:%d\n", code);
    if (code != 1) {
        //update sql ...
        sprintf(update_sql, "UPDATE `solutions` SET `result` = 'res1:%d' WHERE `id` = %d;", code, solution_id);
        update_db(update_sql);
        system(rm_cmd);
        return ;
    }

    //get_judge_result("./judger 1000 2000 /home/yy/web/YOJ/1000.out /home/yy/web/YOJ/data.in /home/yy/web/YOJ/user.out", str);
    get_judge_result(judger_cmd, str);
    code = parse_json(str, "\"code\":");
    printf("res2:%d\n", code);
    if (code != 1) {
        //update sql ...
        sprintf(update_sql, "UPDATE `solutions` SET `result` = 'res2:%d' WHERE `id` = %d;", code, solution_id);
        update_db(update_sql);
        system(rm_cmd);
        return ;
    }

    //get_judge_result("./comparer /home/yy/web/YOJ/data.out /home/yy/web/YOJ/user.out", str);
    get_judge_result(comparer_cmd, str);
    code = parse_json(str, "\"code\":");
    printf("res3:%d\n", code);
    sprintf(update_sql, "UPDATE `solutions` SET `result` = 'res3:%d' WHERE `id` = %d;", code, solution_id);
    update_db(update_sql);
    system(rm_cmd);

}

//./judger 1000 2000 /home/yy/web/YOJ/1000.out /home/yy/web/YOJ/data.in /home/yy/web/YOJ/user.out
void get_judge_result(char cmd[256], char str[256]) {

    int fd[2];
    char buf[256];
    memset(buf, '\0', sizeof(buf));

    if (pipe(fd) != 0) {
        log_msg("error", "pipe init error");
        strcpy(str, "{\"code\":0}");
        return ;
    }

    pid_t child = fork();

    if (child < 0) {

        log_msg("error", "fork error");

        strcpy(str, "{\"code\":0}");

    } else if (child == 0) {

        fflush(stdout);
        close(fd[0]);
        dup2(fd[1], STDOUT_FILENO);

        execl("/bin/bash", "bash", "-c", cmd, NULL);

    } else {

        wait(NULL);
        close(fd[1]);
        read(fd[0], buf, sizeof(buf));
        close(fd[0]);

        strcpy(str, buf);
    }
}


int main()
{
    gohan_init();
    gohan();

    return 0;
}
