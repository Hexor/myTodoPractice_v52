@extends('layouts.app')

@section('content')
    <div class="container">
        <tasks-app></tasks-app>

        <!-- <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Welcome</div>

                    <div class="panel-body">
                        Your Application's Landing Page.1
                    </div>
                </div>

            </div>
        </div> -->
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
                v-show="editedTask == null || editedTask.id != task.id">
                @{{ task.body }}
                </span>

                <input
                        class="edit"
                        type="text"
                        v-model="task.body"
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
    <script src="/javascript/vue-resource.js"></script>
    <script>

        var STORAGE_KEY = 'todos-vuejs-2.0'
        var taskStorage = {
            fetch: function () {
                var todos = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]')
                todos.forEach(function (todo, index) {
                    todo.id = index
                })
                taskStorage.uid = todos.length
                return todos
            },
            save: function (todos) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(todos))
            }
        }

        Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#token').getAttribute('value')
        var resource = Vue.resource('/api/tasks{/id}')

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
                    var vm = this
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
