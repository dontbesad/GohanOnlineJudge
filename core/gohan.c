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
#define MAX_CODE 131072

typedef struct {
    char workdir[LEN];

    char sql_query[LEN];

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
void gohan_core(int, int, int, int);
int  get_source_code(char *, char *);

void log_msg(char error[LEN], char msg[1024]) {
    //记录信息
}

void gohan_init() {

    read_config_file("OJ_WORKDIR=", g_config.workdir);

    read_config_file("OJ_SQL_QUERY=", g_config.sql_query);

    read_config_file("OJ_COMPILER=", g_config.compiler);

    read_config_file("OJ_JUDGER=",    g_config.judger);

    read_config_file("OJ_COMPARER=",  g_config.comparer);

    read_config_file("OJ_DB_HOST=",  g_config.db_host);

    read_config_file("OJ_DB_NAME=",  g_config.db_name);

    read_config_file("OJ_DB_USER=",  g_config.db_user);

    read_config_file("OJ_DB_PASS=",  g_config.db_pass);

    g_config.max_thread = 0;
    char max_thread_str[LEN];
    read_config_file("OJ_THREAD=",   max_thread_str);
    int i, len = strlen(max_thread_str);
    for (i = 0; i < len; ++i) {
        g_config.max_thread = g_config.max_thread * 10 + max_thread_str[i] - '0';
    }

}
/**
 * 读取配置文件
 */
void read_config_file(char match[LEN], char res[LEN]) {

    strcpy(res, "\0");

    FILE *fp = fopen("gohan.conf", "r");

    if (fp == NULL) {
        return ;
    }

    char str[LEN];
    int len = strlen(match);

    while (fgets(str, LEN, fp) != NULL) {

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
int parse_json(char buf[1024], char match[LEN]) {
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

    //char query_sql[] = "SELECT `id` FROM `solutions` WHERE `result` = 'In Queue';";
    //char update_sql[] = "UPDATE `solutions` SET `result` = 'AC' WHERE `id` = 1;";
    int solution_id;
    char update_sql[1024];

    for ( ; ; ) {

        usleep(300000);
        pthread_mutex_lock(&mutex);
        usleep(300000);

        if (solution_id = query_db(g_config.sql_query, 0)) {

            int problem_id = query_db(g_config.sql_query, 1);
            int language   = query_db(g_config.sql_query, 2);

            sprintf(update_sql, "UPDATE `sys_solution` SET `result` = 11 WHERE `solution_id` = %d;", solution_id);
            if (!update_db(update_sql)) {

                sprintf(update_sql, "UPDATE `sys_solution` SET `result` = 0 WHERE `solution_id` = %d;", solution_id);
                pthread_mutex_unlock(&mutex);
                continue;
            }
            pthread_mutex_unlock(&mutex);

            gohan_core(id, solution_id, problem_id, language);

            break;
        }

        pthread_mutex_unlock(&mutex);
    }

    pthread_used[id] = 2; //待释放
    pthread_exit(NULL);
}
//得到mysql中的代码
int get_source_code(char sql[LEN], char source_code[MAX_CODE]) {
    MYSQL      mysql_conn;
    MYSQL_RES  *mysql_result;
    MYSQL_ROW  mysql_row;

    source_code[0] = '\0';

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
        strcpy(source_code, mysql_row[0]);
        //printf("%s\n", source_code);
        return 1;
    }
    return 0;
}

void gohan_core(int runid, int solution_id, int problem_id, int language) {
    /* 获取mysql中存储的源代码 */
    char source_code[MAX_CODE];
    char get_code_sql[LEN];
    sprintf(get_code_sql, "SELECT `source_code` FROM `sys_solution` WHERE `solution_id` = %d;", solution_id);
    int res = get_source_code(get_code_sql, source_code);
    if (!res || source_code[0] == '\0') {
        return ;
    }

    char compiler_cmd[LEN], judger_cmd[LEN], comparer_cmd[LEN];
    char runpath[LEN]; //运行目录
    char sourcepath[LEN]; //源代码保存目录
    char rm_cmd[LEN] = "pwd"; //删除runx目录下的所有文件
    char exec[LEN]   = "Main"; //编译后的文件名
    char source[LEN]; //源代码文件名

    int time_limit, memory_limit;
    char sql_query[LEN];
    sprintf(sql_query, "SELECT `time_limit`,`memory_limit` FROM `sys_problem` WHERE `problem_id` = %d;", problem_id);
    time_limit   = query_db(sql_query, 0);
    memory_limit = query_db(sql_query, 1);

    if (!time_limit || !memory_limit) {
        log_msg("error", "query sys_problem table error");
        return ;
    }

    switch (language) {
        case 2:
            strcpy(source, "Main.cpp");
            break;
        default:
            strcpy(source, "Main.c");
    }

    char user_data[LEN] = "user.out"; //用户输出重定向文件名
    int compile_time = 2000; //编译时间MS

    sprintf(runpath, "%s/run%d", g_config.workdir, runid); //得到线程运行路径
    sprintf(sourcepath, "%s/%s", runpath, source); //得到源代码文件路径

    if (strstr(runpath, "home") != NULL) {
        sprintf(rm_cmd, "rm -rf %s/*", runpath);
    }

    //复制源代码到文件中
    if (access(runpath, F_OK) != 0) {
        mkdir(runpath, 0775); //创建线程运行的目录

        FILE *fp = fopen(sourcepath, "w");
        if (fp == NULL) {
            log_msg("error", "write source file error");
        }
        //fprintf(fp, source_code);
        fwrite(source_code, sizeof(char), MAX_CODE, fp);
        fclose(fp);
    } else if (access(sourcepath, F_OK) != 0) {

        FILE *fp = fopen("Main.cpp", "w");
        if (fp == NULL) {
            log_msg("error", "write source file error");
        }
        //fprintf(fp, source_code);
        fwrite(source_code, sizeof(char), MAX_CODE, fp);
        fclose(fp);
    }

    //不同语言的编译命令
    switch (language) {
        case 1:
            sprintf(compiler_cmd, "%s %d %s/%s \"gcc %s/%s -o %s/%s 2> %s/ce.txt\"",
                g_config.compiler, compile_time, runpath, exec, runpath, source, runpath, exec, runpath);
            break;
        default:
            sprintf(compiler_cmd, "%s %d %s/%s \"g++ %s/%s -o %s/%s 2> %s/ce.txt\"",
                g_config.compiler, compile_time, runpath, exec, runpath, source, runpath, exec, runpath);
    }
    //这里后期需要修改gohan_judger.c文件中的execl运行可执行文件命令
    sprintf(judger_cmd, "%s %d %d %s/%s %s/data/%d/data.in %s/%s",
        g_config.judger, time_limit, memory_limit, runpath, exec, g_config.workdir, problem_id, runpath, user_data);

    sprintf(comparer_cmd, "%s %s/data/%d/data.out %s/%s",
        g_config.comparer, g_config.workdir, problem_id, runpath, user_data);
    //printf("%s\n%s\n%s\n", compiler_cmd, judger_cmd, comparer_cmd);

    char update_sql[LEN];
    char str[LEN];
    int code = 0, runtime = 0, memory = 0;
    //get_judge_result("./compiler 3000 1000.out \"g++ 1000.cpp -o 1000.out 2> CE.txt\"", str);
    //编译阶段
    get_judge_result(compiler_cmd, str); //运行编译程序,将其中的进程运行结果返回给str字符串
    code = parse_json(str, "\"code\":");

    if (code != 1) {
        //update sql ...
        code = get_transform_result(1, code); //转换成实际数据库中对应的结果值
        sprintf(update_sql, "UPDATE `sys_solution` SET `result` = %d WHERE `solution_id` = %d;", code, solution_id);
        update_db(update_sql);
        system(rm_cmd); //删除线程运行目录下的所有文件
        return ;
    }

    //get_judge_result("./judger 1000 2000 /home/yy/web/YOJ/1000.out /home/yy/web/YOJ/data.in /home/yy/web/YOJ/user.out", str);
    //运行阶段
    sprintf(update_sql, "UPDATE `sys_solution` SET `result` = 12 WHERE `solution_id` = %d;", solution_id); //running
    update_db(update_sql);

    get_judge_result(judger_cmd, str);
    code    = parse_json(str, "\"code\":");
    runtime = parse_json(str, "\"runtime\""); //获取运行结果
    memory  = parse_json(str, "\"memory\"");

    if (code != 1) {
        //update sql ...
        code = get_transform_result(2, code);
        sprintf(update_sql, "UPDATE `sys_solution` SET `result` = %d, `runtime` = %d, `memory` = %d WHERE `solution_id` = %d;", code, runtime, memory, solution_id);
        update_db(update_sql);
        system(rm_cmd);
        return ;
    }

    //get_judge_result("./comparer /home/yy/web/YOJ/data.out /home/yy/web/YOJ/user.out", str);
    //对比阶段
    get_judge_result(comparer_cmd, str);
    code = parse_json(str, "\"code\":");
    code = get_transform_result(3, code);
    sprintf(update_sql, "UPDATE `sys_solution` SET `result` = %d, `runtime` = %d, `memory` = %d WHERE `solution_id` = %d;", code, runtime, memory, solution_id);
    update_db(update_sql);
    system(rm_cmd);

}

//./judger 1000 2000 /home/yy/web/YOJ/1000.out /home/yy/web/YOJ/data.in /home/yy/web/YOJ/user.out
void get_judge_result(char cmd[LEN], char str[LEN]) {
    //通过管道来通信两个进程
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

/**
 * 将每个阶段的判断过程转化为总的结果
 */
int get_transform_result(int type, int res) {
    if (type == 1) {
        //阶段一
        switch(res) {
            case 2:
            case 3:
                return 9;
            default:
                break;
        }

    } else if (type == 2) {
        //阶段二
        switch (res) {
            case 2:
                return 5;
            case 3:
                return 6;
            case 4:
                return 7;
            case 5:
                return 8;
            default:
                break;
        }

    } else {
        //阶段三
        switch (res) {
            case 1: //AC
                return 1;
            case 2: //WA
                return 2;
            case 3:
                return 3;
            case 4:
                return 4;
            default:
                break;
        }

    }
    return 10;
}

void gohan() {
    printf("INIT\n");
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

int main()
{
    gohan_init();
    gohan();

    return 0;
}
