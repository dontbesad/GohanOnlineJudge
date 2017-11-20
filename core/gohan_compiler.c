#include <wait.h>
#include <stdio.h>
#include <unistd.h>
#include <string.h>
#include <stdlib.h>
#include <sys/time.h>
#include <sys/user.h>
#include <sys/reg.h>
#include <sys/resource.h>

#define OK     1
#define CE     2
#define CTLE   3 //编译超时
#define SYSERR 4

#define LEN 250

typedef struct {
    long compile_time; //S

    char source_file[LEN];
    char exec_file[LEN];
} gohan_config;

gohan_config g_config;

char compile_cmd[256];

void gohan_compiler();
void child_process();
void set_limit();
void master_process(pid_t);
void delete_exec_file();


void gohan_compiler() {
    pid_t child = fork();
    if (child < 0) {
        exit(-1);
        //printf("err\n");
    }

    if (child == 0) {
        child_process();
    } else {
        master_process(child);
    }
}

void child_process() {
    set_limit();
    execl("/bin/bash", "bash", "-c", compile_cmd, NULL);
}
/* 设置编译的时间 */
void set_limit() {
    /* time limit */
    struct itimerval timer;
    timer.it_value.tv_sec  = g_config.compile_time / 1000;
    timer.it_value.tv_usec = g_config.compile_time % 1000;
    timer.it_interval.tv_sec = timer.it_interval.tv_usec = 0;
    setitimer(ITIMER_REAL, &timer, NULL);
}

void master_process(pid_t child) {
    int status, res;
    struct rusage runinfo;

    wait4(child, &status, 0, &runinfo);

    if (WIFEXITED(status)) {

        res = OK;
        if (access(g_config.exec_file, F_OK) == -1) {
            res = CE; //可执行文件不存在
        }
    } else if (WIFSIGNALED(status)) {

        int sign = WTERMSIG(status);

        if (sign == SIGALRM) {

            res = CTLE;
            kill(child, SIGKILL);
        } else {

            res = SYSERR;
            kill(child, SIGKILL);
            delete_exec_file();
        }

    } else {

        res = SYSERR;
        delete_exec_file();
    }
    printf("%d\n", res);
    exit(res);
}

void delete_exec_file() {
    if (access(g_config.exec_file, F_OK) != -1) {
        remove(g_config.exec_file);
    }
}

/**
 * eg:
 * ./compiler[编译程序] 1000[编译时间] 1000.out[可执行程序] "g++ 1000.cpp -o 1000.out 2> CE.txt"[命令]
 */

int main(int argc, char **argv)
{
    if (argc != 4) {
        /* 参数个数不正确 */
        exit(0);
    }
    int i = 0;
    for ( ; i < argc; ++i) {
        if (argv[i] == NULL) {
            exit(0);
        }
    }
    i = 0;
    /* 设置编译时间限制 */
    g_config.compile_time = 0;
    int len = strlen(argv[1]);
    for ( ; i < len; ++i) {
        g_config.compile_time = g_config.compile_time * 10 + argv[1][i] - '0';
    }
    printf("%ld\n", g_config.compile_time);

    /*strcpy(g_config.source_file, "1000.cpp");
    strcpy(g_config.exec_file  , "1000.out");
    sprintf(compile_cmd, "g++ %s -o %s 2> ce.txt", g_config.source_file, g_config.exec_file);*/

    /* 设置编译出来的可执行文件 */
    strcpy(g_config.exec_file  , argv[2]);

    /* 设置编译命令 */
    strcpy(compile_cmd, argv[3]);
    printf("%s\n", compile_cmd);

    gohan_compiler();

    return 0;
}
