// Teachers autocomplete handler.
var Teachers = {
    teachers : [],
    init : function() {
       Teachers.teachers = [];
       Teachers.update();
    },
    select : function (selected) {
        if (typeof selected.result.raw.id != "undefined") {
            if (ncccs_contains(Teachers.teachers, selected.result.raw) == false) {
                Teachers.teachers.push(selected.result.raw);
            }
        }
        Teachers.update();
        setTimeout(function () { Y.one('#id_teacherids').set('value', ''); }, 250);
    },
    delete : function(id) {
       var newlist = [];
       for(var i = 0; i < Teachers.teachers.length; i++) {
            if (Teachers.teachers[i]['id'] != id) {
                newlist.push(Teachers.teachers[i]);
            }
       }
       Teachers.teachers = newlist;
       Teachers.update();
    },
    update : function() {
       var html = "";
       var ids = "";
       var seperator = "";
       for(var i = 0; i < Teachers.teachers.length; i++) {
           html += '<a href="javascript:Teachers.delete('+Teachers.teachers[i]['id']+')"><img src="images/delete.png" style="vertical-align: -2px"></a> '+Teachers.teachers[i].name+'<br>';
           ids = ids + seperator + Teachers.teachers[i]['id'];
           seperator = ',';
       }
       if (html != "") {
           html = '<b>Teachers selected</b><br>'+html;
       }
       ncccs_setcontent('#teachers_list', html);
       Y.one('input[name="teachers"]').set('value', ids);
    }
}

// Categories autocomplete handler.
var Categories = {
    categories : [],
    init : function() {
       Categories.categories = [];
       Categories.update();
    },
    select : function (selected) {
        if (typeof selected.result.raw.id != "undefined") {
            if (ncccs_contains(Categories.categories, selected.result.raw) == false) {
                Categories.categories.push(selected.result.raw);
            }
        }
        Categories.update();
        setTimeout(function () { Y.one('#id_categoryids').set('value', ''); }, 250);
    },
    delete : function(id) {
       var newlist = [];
       for(var i = 0; i < Categories.categories.length; i++) {
            if (Categories.categories[i]['id'] != id) {
                newlist.push(Categories.categories[i]);
            }
       }
       Categories.categories = newlist;
       Categories.update();
    },
    update : function() {
       var html = "";
       var ids = "";
       var seperator = "";
       for(var i = 0; i < Categories.categories.length; i++) {
           html += '<a href="javascript:Categories.delete('+Categories.categories[i]['id']+')"><img src="images/delete.png" style="vertical-align: -2px"></a> '+Categories.categories[i].name+'<br>';
           ids = ids + seperator + Categories.categories[i]['id'];
           seperator = ',';
       }
       if (html != "") {
           html = '<b>Categories selected</b><br>'+html;
       }
       ncccs_setcontent('#categories_list', html);
       Y.one('input[name="categories"]').set('value', ids);
    }
}

// Courses autocomplete handler.
var Courses = {
    courses : [],
    init : function() {
       Courses.courses = [];
       Courses.update();
    },
    select : function (selected) {
        if (typeof selected.result.raw.id != "undefined") {
            if (ncccs_contains(Courses.courses, selected.result.raw) == false) {
                Courses.courses.push(selected.result.raw);
            }
        }
        Courses.update();
        setTimeout(function () { Y.one('#id_coursesids').set('value', ''); }, 250);
    },
    delete : function(id) {
       var newlist = [];
       for(var i = 0; i < Courses.courses.length; i++) {
            if (Courses.courses[i]['id'] != id) {
                newlist.push(Courses.courses[i]);
            }
       }
       Courses.courses = newlist;
       Courses.update();
    },
    update : function() {
       var html = "";
       var ids = "";
       var seperator = "";
       for(var i = 0; i < Courses.courses.length; i++) {
           html += '<a href="javascript:Courses.delete('+Courses.courses[i]['id']+')"><img src="images/delete.png" style="vertical-align: -2px"></a> '+Courses.courses[i].name+'<br>';
           ids = ids + seperator + Courses.courses[i]['id'];
           seperator = ',';
       }
       if (html != "") {
           html = '<b>Courses selected</b><br>'+html;
       }
       ncccs_setcontent('#courses_list', html);
       Y.one('input[name="courses"]').set('value', ids);
    }
}

// Setup autocomplete handlers.
function init_ncccsautocomplete(Y) {
    // Not on auto complete page.
    if (Y.one('#id_teacherids') != null) {
        // Add query prameter for course categories.
        source = 'teachers.php?q={query}&callback={callback}&cc=';
        source = source + encodeURIComponent(JSON.stringify(Categories.categories));

        // Add query parameter for courses.
        source = source + '&c=';
        source = source + encodeURIComponent(JSON.stringify(Courses.courses));

        Y.one('#id_teacherids').plug(Y.Plugin.AutoComplete, {
            resultFilters    : 'phraseMatch',
            resultHighlighter: 'phraseMatch',
            resultTextLocator: 'name',
            requestTemplate  : teacherSearch,
            source           : source,
            on               : {
                select : Teachers.select
            }
        });
    }

    if (Y.one('#id_categoryids') != null) {
        Y.one('#id_categoryids').plug(Y.Plugin.AutoComplete, {
            resultFilters    : 'phraseMatch',
            resultHighlighter: 'phraseMatch',
            resultTextLocator: 'name',
            source           : 'categories.php?q={query}&callback={callback}',
            on               : {
                select : Categories.select
            }
        });
    }

    if (Y.one('#id_coursesids') != null) {
        source = 'courses.php?q={query}&callback={callback}&c=';
        source = source + encodeURIComponent(JSON.stringify(Categories.categories));
        Y.one('#id_coursesids').plug(Y.Plugin.AutoComplete, {
            resultFilters    : 'phraseMatch',
            resultHighlighter: 'phraseMatch',
            resultTextLocator: 'name',
            requestTemplate  : courseSearch,
            source           : source,
            on               : {
                select : Courses.select
            }
        });
    }
    if (Y.one('#id_resetbutton') != null) {
        Y.one('#id_resetbutton').on('click', function () { Categories.init(); Courses.init(); Teachers.init(); });
    }
}

// Rewrite courses query url to include categories.
function courseSearch(query) {
    var url = 'q='+encodeURIComponent(query)+'&c=';
    url = url + encodeURIComponent(JSON.stringify(Categories.categories));
    return url;
}

// Rewrite courses query url to include categories.
function teacherSearch(query) {
    var url = 'q='+encodeURIComponent(query)+'&cc=';
    url = url+encodeURIComponent(JSON.stringify(Categories.categories));
    url = url+'&c=';
    url = url+encodeURIComponent(JSON.stringify(Courses.courses));
    return url;
}

// Check to see if id exists in array.
function ncccs_contains(a, obj) {
    var i = a.length;
    while (i--) {
        if (a[i]['id'] === obj['id']) {
            return true;
        }
    }
    return false;
}

// Set content and visibility.
function ncccs_setcontent(id, html) {
    var div = Y.one(id);
    if (div == null) {
        return;
    }
    div.setContent(html);
    if (html != '') {
       div.setStyle("display", "block");
    } else {
       div.setStyle("display", "none");
    }
}
