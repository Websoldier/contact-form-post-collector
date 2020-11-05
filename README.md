```
/*
 * Plugin Name: Reviews for WPCF7
 * Plugin URI: https://github.com/nikolays93/reviews-and-questions
 * Description: Add site reviews and questions support with contact form 7.
 * Version: 1.0
 * Author: NikolayS93
 * Author URI: https://vk.com/nikolays_93
 * Author EMAIL: NikolayS93@ya.ru
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpcf7-reviews
 * Domain Path: /languages/
 */
 ```

## Как это работает? ##
Для того чтобы создать новый отзыв в базе данных нужно указать тип записи - __куда нужно поместить новую запись__. К примеру, это можно сделать так:  

`<input type="hidden" name="_cf7_save" value="post">`  

При отправке такого сообщения: __плагин создаст новую запись с типом post (Запись) в статусе pending (На утверждении)__. В заголовке будет указана дата отправки сообщения. Все поля не начинающиеся с нижнего подчеркивания (\_example) будут записаны в дополнительные поля (Custom post meta)
