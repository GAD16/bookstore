$(document).ready(initPage);

function initPage () {

    $('.popup_bg').click(function () {	// Событие клика на затемненный фон
        $('.popup').fadeOut(800);	// Медленно убираем всплывающее окно
        $('.updateButton').hide(800); //Спрятать кнопку
        $('.addButton').hide(800); //Спрятать кнопку


    });


    $('.tableBlock').on('click', '.delButton', function () { //Событие клика на красный крестик
        var id = $(this).parent().attr('data-id');
        console.log(id);
        $.ajax({
            type: "GET",
            url: "delButton",
            data: {id: id},
            success: function (data) {
                if (!data) {
                    alert('Ошибка. Не удалось удалить запись');
                }
                else {

                    $('[data-id='+id+']').parent().remove();
                }
            }
        });
    });
    $('.tableBlock').on('click', '.cell', function () { //Событие клика по данным из таблицы
        $('.popup').fadeIn(800);
        $('.updateButton').css('display', 'inline');
        var row = $(this).parent().parent();
        id = $(row).children('.setID').attr('data-id');
        row = $(row).children();
        var inputs = $('.inputBox').children();
        var data = $(row).eq(0).text();
        $(inputs).eq(0).attr({'placeholder': data});
        data = $(row).eq(1).text();
        $(inputs).eq(1).attr({'placeholder': data});
        data = $(row).eq(2).text();
        $(inputs).eq(2).attr({'placeholder': data});
        data = $(row).eq(3).text();
        $(inputs).eq(3).attr({'placeholder': data});
        var result = [];

        $('.updateButton').one('click', function () { //Клик по кнопке заменить данные
            $('.popup').fadeOut(800);
            $('.updateButton').hide(800);
            result = $('form').serializeArray();
            result[4] = {'name': 'id', 'value': id};
            result = $.toJSON(result);
            $('.updInput').val('');
            $.ajax({
                type: "POST",
                url: "updateButton",
                data: {data: result},
                success: function (data) {
                    if (data === '0') {
                        alert('Ошибка. Не удалось изменить запись');

                    } else {
                        $('[data-id = ' + id + ']').parent().html(data);
                    }
                }
            });
        });
    });

    $('.addBtnOnPage').click(function () { //заполняет плейсхолдеры формы при клике по кнопке добавить книгу
        $('.popup').fadeIn(800);
        $('.addButton').css('display', 'inline');
        var inputs = $('.inputBox').children();
        var data = ['Жанры', 'Автор', 'Название книги', 'Год издания'];
        $(inputs).eq(0).attr({'placeholder': data[0]});
        $(inputs).eq(1).attr({'placeholder': data[1]});
        $(inputs).eq(2).attr({'placeholder': data[2]});
        $(inputs).eq(3).attr({'placeholder': data[3]});

        $('.addButton').one('click', function () { //Клик по кнопке добавить книгу
            $('.popup').fadeOut(800);
            $('.addButton').hide(800);
            result = $('form').serializeArray();
            result = $.toJSON(result);
            console.log (result);
           $('.updInput').val('');
            $.ajax({
                type: "POST",
                url: "addButton",
                data: {data: result},
                success: function (data) {
                    console.log(data);
                    switch (data) {
                        default:
                            $('tbody').append('<tr>' + data + '</tr>');
                            data = '';
                            break;
                        case 'не все поля заполнены':
                            alert(data);
                            data = '';
                            break;
                        case 'такая книга уже добавлена':
                            alert(data);
                            data = '';
                            break;
                        case 'не верный формат введенных данных':
                            alert(data);
                            data = '';
                            break;
                        case 'не верный формат года':
                            alert(data);
                            data = '';
                            break;
                        case 'год написания книги не может быть больше текущего':
                            alert(data);
                            data = '';
                            break;
                        }
                    }
                });
            });
        });
    }
