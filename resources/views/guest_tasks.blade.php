@extends('layouts.app')

@section('content')
    <div class="container">
        <tasks-app></tasks-app>
    </div>

    <template id="tasks-template">
        <form class="form-group" @submit="createTask">
        <input type="text" class="form-control" v-model="newTaskContent">
        <br />
        <button type="submit" class="btn btn-success btn-block">Create One Todo</button>
        </form>
        <br />
        <ul class="list-group">
            <li class="list-group-item" v-for="task in list | orderBy 'id' -1">
                <input v-model="task.completed" @change="onClickCheckbox(task)" type="checkbox">
                <span
                    @click="editTask(task)"
                    {{--当编辑这个任务时，隐藏原有的文字--}}
                    v-show="editedTask == null || editedTask.id != task.id">
                    @{{ task.body }}
                </span>

                <input
                        class="edit"
                        type="text"
                        v-model="task.body"
                        {{--当编辑这个任务时，显示编辑框，并自动获得焦点--}}
                        v-show="editedTask != null && editedTask.id == task.id"
                        v-task-focus="editedTask != null && editedTask.id == task.id"
                        @blur="doneEdit(task)"
                        @keyup.enter="doneEdit(task)"
                        @keyup.esc="cancelEdit(task)"
                >
                <strong @click="deleteTask(task)">X</strong>
            </li>
        </ul>
    </template>

    <script src="/javascript/vue.js"></script>

    <script>

        var TODO_STORAGE_KEY = 'todos'

        var taskStorage = {
            fetch: function () {
                // 查看本地是否有保存过的缓存文件，如果没有，就将 todos 初始化为空数组 []
                var todos = JSON.parse(localStorage.getItem(TODO_STORAGE_KEY) || '[]')
                todos.forEach(function (todo, index) {
                    todo.id = index
                })
                taskStorage.uid = todos.length
                return todos
            },

            save: function (todos) {
                localStorage.setItem(TODO_STORAGE_KEY, JSON.stringify(todos))
            }
        }

        Vue.component('tasks-app',{
            template:'#tasks-template',

            data:function () {
                return {
                    newTaskContent:'',
                    list:taskStorage.fetch(),
                    editedTask: null,
                }
            },

            watch: {
                // 监听 list 对象的内容变化，发生任何变化都将触发 save 方法
                list: {
                    handler: function (list) {
                        taskStorage.save(list)
                    },
                    deep: true
                }
            },

            methods:{
                editTask: function (task) {
                    this.beforeEditCache = task.body
                    this.editedTask = task
                },


                cancelEdit: function (task) {
                    console.log("esc")
                    this.editedTodo = null
                    task.body = this.beforeEditCache
                },

                doneEdit: function (task) {
                    // 用户使用 enter 回车键结束编辑的时候，会先触发 keyup.enter ，然后再触发一次 blur
                    if (!this.editedTask) {
                        return
                    }
                    this.editedTask = null

                    task.body = task.body.trim()
                    if (!task.body) {
                        this.deleteTask(task)
                    }
                },

                deleteTask:function (task) {
                    this.list.splice(this.list.indexOf(task), 1)
                },

                createTask:function (e) {
                    e.preventDefault()
                    var content = this.newTaskContent && this.newTaskContent.trim()

                    if (!content) {
                        return
                    }

                    this.list.push({
                        id: taskStorage.uid++,
                        body: content,
                        completed: false,
                    })

                    this.newTaskContent = ''
                }
            },

            directives: {
                'task-focus': function (value) {
                    if (!value) {
                        return;
                    }
                    var el = this.el;
                    Vue.nextTick(function () {
                        el.focus();
                    });
                }
            }

        })

        new Vue({
            el:'body'
        });
    </script>
@endsection
