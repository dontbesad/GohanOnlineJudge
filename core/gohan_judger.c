/**
 * Gohan Online Judge Core Code
 */
#include <wait.h>
#include <stdio.h>
#include <unistd.h>
#include <stdlib.h>
#include <string.h>
#include <sys/reg.h>
#include <sys/user.h>
#include <sys/time.h>
#include <sys/types.h>
#include <sys/ptrace.h>
#include <sys/resource.h>

#if __WORDSIZE == 64
    #define SYSCALL_NUM(reg) reg.orig_rax
#else
    #define SYSCALL_NUM(reg) reg.orig_eax
#endif

#define OK     1
#define TLE    2
#define MLE    3
#define RE     4
#define MC     5
#define SYSERR 6 /* unknown error */

#define max(a, b)   a > b ? a : b
#define SYSCALL_MAX 350
#define LEN         250

typedef struct {
    char exec_path[LEN];
    char data_in_path[LEN];
    char data_out_path[LEN];

    long limit_time;
    long limit_memory;
} gohan_config;

typedef struct {
    long runtime;
    long memory;
    int  res;
} gohan_result;

int gohan_syscall_whitelist[] = {0, 1, 2, 3, 4, 5, 8, 9, 10, 11, 12, 21, 59, 63, 89, 158, 231, 240, 252};
int gohan_valid_syscall[SYSCALL_MAX];

gohan_config g_config;
gohan_result g_result;

void gohan_test(pid_t child) {
    char name[LEN];
    sprintf(name, "/proc/%d/statm", child);
    FILE *fp = fopen(name, "r");
    if (fp == NULL) {
        //printf("Not exist\n");
        return ;
    }

    while (fgets(name, LEN - 1, fp)) {
        name[LEN - 1] = '\0';
        printf("%s\n", name);
    }
}

void gohan_init();
void gohan_judger();
    void child_process();
        void set_limit();
    void master_process(pid_t);
        long get_child_memory(pid_t, struct rusage);
        long get_child_runtime(struct rusage);
        int check_syscall_valid(int);


void gohan_init() {

    int i = 0, white_len = sizeof(gohan_syscall_whitelist) / sizeof(int);
    memset(gohan_valid_syscall, 0, sizeof(gohan_valid_syscall));

    for ( ; i < white_len; ++i) {
        gohan_valid_syscall[gohan_syscall_whitelist[i]] = 1;
    }
}

void gohan_judger() {
    pid_t child = fork();
    if (child < 0) {
        g_result.res = SYSERR;
        printf("{\"code\":0,\"runtime\":0,\"memory\":0}");
        exit(0);
    }

    if (child == 0) {
        child_process();
    } else {
        master_process(child);
    }
}

void child_process() {
    set_limit();

    ptrace(PTRACE_TRACEME, 0, NULL, NULL);

    freopen(g_config.data_in_path,  "r", stdin);
    freopen(g_config.data_out_path, "w", stdout);
    freopen("/dev/null", "a", stderr);

    execl(g_config.exec_path, g_config.exec_path, NULL);
}

void set_limit() {
    /* 内存限制,大多情况下会判断RE,还是直接在程序中判断即可 */
    /*struct rlimit lim_memory;
    lim_memory.rlim_cur = 1024 * g_config.limit_memory;
    lim_memory.rlim_max = lim_memory.rlim_cur * 200;
    setrlimit(RLIMIT_DATA, &lim_memory);*/

    /* MS级别时间限制,会将所有进程的时间算上 */
    // struct itimerval lim_time;
    // lim_time.it_value.tv_sec  = g_config.limit_time / 1000; /* 秒 */
    // lim_time.it_value.tv_usec = g_config.limit_time % 1000 * 1000; /* 微秒 */
    // lim_time.it_interval.tv_sec = lim_time.it_interval.tv_usec = 0;
    // setitimer(ITIMER_REAL, &lim_time, NULL);

    struct rlimit limit;
    limit.rlim_cur = g_config.limit_time / 1000 + (g_config.limit_time % 1000 ? 1 : 0);
    limit.rlim_max = limit.rlim_cur + 2;
    setrlimit(RLIMIT_CPU, &limit);
}

void master_process(pid_t child) {

    int status, judge_res, sign;
    long judge_runtime, judge_memory, max_mem = -1;
    struct rusage runinfo;
    struct user_regs_struct reginfo;
    int cnt = 0;

    while (1) {

        wait4(child, &status, 0, &runinfo); /* 阻塞 */

        long tmp = get_child_memory(child, runinfo);

        judge_memory = tmp == -1 ? judge_memory : tmp;
        max_mem = max(judge_memory, max_mem);

        judge_runtime = get_child_runtime(runinfo);

        if (judge_runtime > g_config.limit_time) {
            judge_res = TLE;
            kill(child, SIGKILL);
            break;
        }

        if (judge_memory > g_config.limit_memory) {
            judge_res = MLE;
            kill(child, SIGKILL);
            break;
        }

        if (WIFEXITED(status)) {

            judge_res = OK;
            break;
        } else if (WIFSTOPPED(status)) {

            sign = WSTOPSIG(status);

            switch (sign) {

                case SIGALRM:
                case SIGXCPU:
                    judge_res = TLE;
                    goto _target;
                case SIGFPE:
                    judge_res = RE; /* /0 */
                    goto _target;
                case SIGSEGV:
                    judge_res = RE;
                    goto _target;
                case SIGTRAP:
                    ptrace(PTRACE_GETREGS, child, NULL, &reginfo);

                    int syscall_num = SYSCALL_NUM(reginfo);
                    if (!check_syscall_valid(syscall_num)) {
                        judge_res = MC;
                        kill(child, SIGKILL);
                        goto _target;
                    }

                    ptrace(PTRACE_SYSCALL, child, NULL, NULL);
                    break;
                default:
                    judge_res = SYSERR;
                    goto _target;
            }

        } else if (WIFSIGNALED(status)) {

            judge_res = SYSERR;
            sign = WTERMSIG(status);
            kill(child, SIGKILL);
            break;
        } else {

            judge_res = SYSERR;
            kill(child, SIGKILL);
            break;
        }
    }

_target:
    g_result.res     = judge_res;
    g_result.runtime = judge_runtime;
    g_result.memory  = judge_memory;
    //printf("judge_result: %d, time: %ldMS, mem: %ldKB\n", judge_res, judge_runtime, judge_memory);
    //printf("Max Mem: %ld\n", max_mem);

    char ret[LEN];
    sprintf(ret, "{\"code\":%d,\"runtime\":%ld,\"memory\":%ld}", judge_res, judge_runtime, judge_memory);
    printf("%s", ret);
    exit(judge_res);
}

long get_child_memory(pid_t child, struct rusage runinfo) {
    //return runinfo.ru_maxrss;

    char proc_file[30];
    char *option = "VmData:";
    long memory = -1;

    sprintf(proc_file, "/proc/%d/status", (int)child);
    FILE *fp = fopen(proc_file, "r");

    if (fp == NULL) {
        return memory;
    }

    char name[LEN];
    int itemlen = strlen(option);

    while (fgets(name, LEN, fp)) {
        name[LEN - 1] = '\0';
        if (strncmp(option, name, itemlen) == 0) {
            sscanf(name + itemlen + 1, "%ld", &memory);
            break;
        }
    }
    return memory;
}

long get_child_runtime(struct rusage runinfo) {
    long user_runtime = runinfo.ru_utime.tv_sec * 1000 + runinfo.ru_utime.tv_usec / 1000;
    long sys_runtime  = runinfo.ru_stime.tv_sec * 1000 + runinfo.ru_stime.tv_usec / 1000;
    return user_runtime + sys_runtime;
}

int check_syscall_valid(int syscall_num) {
    //sys_open之后处理
    return gohan_valid_syscall[syscall_num];
}

/**
 * eg:
 * ./judger 1000 2000 /home/yy/web/YOJ/1000.out /home/yy/web/YOJ/data.in /home/yy/web/YOJ/user.out
 */

int main(int argc, char **argv)
{
    if (argc != 6) {
        exit(0);
    }

    g_config.limit_time   = 0;
    g_config.limit_memory = 0;

    int len = strlen(argv[1]), i = 0;
    for ( ; i < len; ++i) {
        g_config.limit_time = g_config.limit_time * 10 + argv[1][i] - '0';
    }

    len = strlen(argv[2]), i = 0;
    for ( ; i < len; ++i) {
        g_config.limit_memory = g_config.limit_memory * 10 + argv[2][i] - '0';
    }

    strcpy(g_config.exec_path,     argv[3]);
    strcpy(g_config.data_in_path,  argv[4]);
    strcpy(g_config.data_out_path, argv[5]);

    gohan_init();
    gohan_judger();
    return 0;
}
