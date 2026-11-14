<?php
/**
 * Sunny World — NỘI DUNG MẪU, chỉ nạp vào CSDL một lần ở lần chạy đầu.
 * Sau đó admin sửa trong trang quản trị (Từ vựng, Truyện).
 *
 * Toàn bộ nội dung dưới đây do nhóm làm website tự viết (hai truyện ngụ ngôn là kể lại truyện Aesop).
 * Giáo viên của Sunny cần rà lại nghĩa tiếng Việt và kiến thức khoa học trước khi công bố.
 */
declare(strict_types=1);

function sw_seed_data(): array
{
    // Chủ đề từ vựng: [slug, tên tiếng Anh, tên tiếng Việt, màu, hình đại diện, [ [từ, nghĩa, câu ví dụ, nghĩa câu, hình], ... ]]
    $topics = [
        ['animals', 'Animals', 'Con vật', '#F7931E', 'cat', [
            ['cat', 'con mèo', 'The cat is sleeping.', 'Con mèo đang ngủ.', 'cat'],
            ['dog', 'con chó', 'My dog is big.', 'Con chó của tớ to lắm.', 'dog'],
            ['bird', 'con chim', 'The bird can fly.', 'Con chim biết bay.', 'bird'],
            ['fish', 'con cá', 'The fish is in the water.', 'Con cá ở trong nước.', 'fish'],
            ['rabbit', 'con thỏ', 'The rabbit likes carrots.', 'Con thỏ thích cà rốt.', 'rabbit'],
            ['duck', 'con vịt', 'The duck says quack.', 'Con vịt kêu quạc quạc.', 'duck'],
            ['pig', 'con heo (con lợn)', 'The pig is pink.', 'Con heo màu hồng.', 'pig'],
            ['cow', 'con bò', 'The cow gives us milk.', 'Con bò cho chúng ta sữa.', 'cow'],
        ]],
        ['fruits', 'Fruits', 'Trái cây', '#E53935', 'apple', [
            ['apple', 'quả táo', 'I eat an apple.', 'Tớ ăn một quả táo.', 'apple'],
            ['banana', 'quả chuối', 'The banana is yellow.', 'Quả chuối màu vàng.', 'banana'],
            ['orange', 'quả cam', 'This orange is sweet.', 'Quả cam này ngọt.', 'orange'],
            ['grapes', 'quả nho', 'I like grapes.', 'Tớ thích nho.', 'grapes'],
            ['strawberry', 'quả dâu tây', 'The strawberry is red.', 'Quả dâu tây màu đỏ.', 'strawberry'],
            ['watermelon', 'quả dưa hấu', 'The watermelon is big.', 'Quả dưa hấu to lắm.', 'watermelon'],
            ['lemon', 'quả chanh vàng', 'The lemon is sour.', 'Quả chanh thì chua.', 'lemon'],
            ['cherry', 'quả anh đào', 'I have two cherries.', 'Tớ có hai quả anh đào.', 'cherry'],
        ]],
        ['colors', 'Colors', 'Màu sắc', '#8E24AA', 'color-purple', [
            ['red', 'màu đỏ', 'The apple is red.', 'Quả táo màu đỏ.', 'color-red'],
            ['blue', 'màu xanh dương', 'The sky is blue.', 'Bầu trời màu xanh dương.', 'color-blue'],
            ['green', 'màu xanh lá', 'The leaf is green.', 'Chiếc lá màu xanh lá.', 'color-green'],
            ['yellow', 'màu vàng', 'The sun is yellow.', 'Mặt trời màu vàng.', 'color-yellow'],
            ['orange', 'màu cam', 'The carrot is orange.', 'Củ cà rốt màu cam.', 'color-orange'],
            ['pink', 'màu hồng', 'The flower is pink.', 'Bông hoa màu hồng.', 'color-pink'],
            ['purple', 'màu tím', 'The grapes are purple.', 'Chùm nho màu tím.', 'color-purple'],
            ['black', 'màu đen', 'The cat is black.', 'Con mèo màu đen.', 'color-black'],
        ]],
        ['numbers', 'Numbers', 'Số đếm', '#1E88E5', 'num-3', [
            ['one', 'số một', 'I have one nose.', 'Tớ có một cái mũi.', 'num-1'],
            ['two', 'số hai', 'I have two eyes.', 'Tớ có hai con mắt.', 'num-2'],
            ['three', 'số ba', 'I see three little birds.', 'Tớ thấy ba chú chim nhỏ.', 'num-3'],
            ['four', 'số bốn', 'A dog has four legs.', 'Con chó có bốn cái chân.', 'num-4'],
            ['five', 'số năm', 'I have five fingers.', 'Tớ có năm ngón tay.', 'num-5'],
            ['six', 'số sáu', 'I have six red apples.', 'Tớ có sáu quả táo đỏ.', 'num-6'],
            ['seven', 'số bảy', 'There are seven days in a week.', 'Một tuần có bảy ngày.', 'num-7'],
            ['eight', 'số tám', 'A spider has eight legs.', 'Con nhện có tám cái chân.', 'num-8'],
            ['nine', 'số chín', 'I see nine yellow stars.', 'Tớ thấy chín ngôi sao vàng.', 'num-9'],
            ['ten', 'số mười', 'I have ten toes.', 'Tớ có mười ngón chân.', 'num-10'],
        ]],
        ['school', 'School things', 'Đồ dùng học tập', '#43A047', 'pencil', [
            ['book', 'quyển sách', 'Open your book.', 'Hãy mở sách ra.', 'book'],
            ['pencil', 'bút chì', 'I write with a pencil.', 'Tớ viết bằng bút chì.', 'pencil'],
            ['bag', 'cặp sách', 'My bag is orange.', 'Cặp sách của tớ màu cam.', 'bag'],
            ['ruler', 'thước kẻ', 'The ruler is long.', 'Cây thước thì dài.', 'ruler'],
            ['eraser', 'cục gôm (cục tẩy)', 'Can I have an eraser?', 'Cho tớ mượn cục gôm được không?', 'eraser'],
            ['clock', 'đồng hồ', 'Look at the clock.', 'Hãy nhìn đồng hồ.', 'clock'],
            ['board', 'cái bảng', 'The teacher writes on the board.', 'Cô giáo viết lên bảng.', 'board'],
            ['chair', 'cái ghế', 'Sit on the chair.', 'Hãy ngồi lên ghế.', 'chair'],
        ]],
        ['weather', 'Weather', 'Thời tiết', '#FDBE10', 'sun', [
            ['sun', 'mặt trời', 'The sun is hot.', 'Mặt trời thì nóng.', 'sun'],
            ['cloud', 'đám mây', 'The cloud is white.', 'Đám mây màu trắng.', 'cloud'],
            ['rain', 'mưa', 'I like the rain.', 'Tớ thích mưa.', 'rain'],
            ['snow', 'tuyết', 'The snow is cold.', 'Tuyết thì lạnh.', 'snow'],
            ['wind', 'gió', 'The wind is strong.', 'Gió thổi mạnh.', 'wind'],
            ['rainbow', 'cầu vồng', 'Look! A rainbow!', 'Nhìn kìa! Cầu vồng!', 'rainbow'],
        ]],
        ['shapes', 'Shapes', 'Hình khối', '#F06292', 'star', [
            ['circle', 'hình tròn', 'The ball is a circle.', 'Quả bóng có hình tròn.', 'circle'],
            ['square', 'hình vuông', 'The window is a square.', 'Cửa sổ có hình vuông.', 'square'],
            ['triangle', 'hình tam giác', 'A triangle has three sides.', 'Hình tam giác có ba cạnh.', 'triangle'],
            ['rectangle', 'hình chữ nhật', 'The door is a rectangle.', 'Cánh cửa có hình chữ nhật.', 'rectangle'],
            ['star', 'ngôi sao', 'I see a star.', 'Tớ thấy một ngôi sao.', 'star'],
            ['heart', 'hình trái tim', 'I draw a heart.', 'Tớ vẽ một hình trái tim.', 'heart'],
        ]],
    ];

    // Truyện: [slug, loại, tên tiếng Anh, tên tiếng Việt, hình bìa, màu, [ [trang tiếng Anh, nghĩa cả trang], ... ], "từ mới = nghĩa"]
    $stories = [
        ['the-little-sun', 'story', 'The Little Sun', 'Mặt trời nhỏ', 'sun', '#FFE9A8', [
            ['Sunny is a little sun. He lives in the sky. He is very shy.', 'Sunny là một mặt trời nhỏ. Cậu ấy sống trên bầu trời. Cậu ấy rất nhút nhát.'],
            ['Every morning, the birds say, "Hello, Sunny!" But Sunny does not say hello. He hides behind a cloud.', 'Mỗi buổi sáng, những chú chim nói: "Xin chào, Sunny!" Nhưng Sunny không chào lại. Cậu ấy trốn sau một đám mây.'],
            ['One day, a small bird is sad. She is lost. Sunny wants to help her.', 'Một ngày nọ, có một chú chim nhỏ rất buồn. Chú bị lạc đường. Sunny muốn giúp chú.'],
            ['Sunny says, "Hello! I can help you." He shines on the way home. The bird is happy.', 'Sunny nói: "Xin chào! Tớ có thể giúp bạn." Cậu ấy chiếu sáng đường về nhà. Chú chim rất vui.'],
            ['Now Sunny says hello every day. He has many friends.', 'Bây giờ ngày nào Sunny cũng nói xin chào. Cậu ấy có rất nhiều bạn.'],
        ], "shy = nhút nhát\nhides = trốn\ncloud = đám mây\nlost = bị lạc\nshines = chiếu sáng"],
        ['the-red-ball', 'story', 'The Red Ball', 'Quả bóng đỏ', 'ball', '#FBD3CF', [
            ['Tom has a red ball. He plays with it in the garden.', 'Tom có một quả bóng đỏ. Cậu ấy chơi với nó trong vườn.'],
            ['The ball jumps over the wall. Tom cannot see it.', 'Quả bóng nảy qua bức tường. Tom không nhìn thấy nó nữa.'],
            ['A big dog comes. The dog has the ball in its mouth!', 'Một con chó to đi tới. Con chó đang ngậm quả bóng trong miệng!'],
            ['"Thank you, dog!" says Tom. Now Tom and the dog play together.', '"Cảm ơn bạn chó nhé!" Tom nói. Bây giờ Tom và chú chó cùng chơi với nhau.'],
        ], "garden = khu vườn\njumps = nảy lên\nwall = bức tường\nmouth = cái miệng\ntogether = cùng nhau"],
        ['the-lion-and-the-mouse', 'story', 'The Lion and the Mouse', 'Sư tử và chuột nhắt', 'lion', '#FFE0B2', [
            ['A big lion sleeps under a tree. A little mouse runs on his nose.', 'Một con sư tử to đang ngủ dưới gốc cây. Một chú chuột nhắt chạy lên mũi sư tử.'],
            ['The lion wakes up. He is angry. "Please let me go," says the mouse. "One day I will help you."', 'Sư tử thức dậy. Nó rất tức giận. "Xin hãy thả tôi đi," chuột nói. "Một ngày nào đó tôi sẽ giúp ông."'],
            ['The lion laughs, but he lets the mouse go.', 'Sư tử bật cười, nhưng nó thả cho chuột đi.'],
            ['The next day, the lion is in a net. He cannot get out. The mouse hears him.', 'Ngày hôm sau, sư tử bị mắc trong một cái lưới. Nó không thoát ra được. Chuột nghe thấy tiếng sư tử.'],
            ['The mouse bites the net. The lion is free! "Thank you, little friend," says the lion.', 'Chuột cắn đứt cái lưới. Sư tử được tự do! "Cảm ơn nhé, người bạn nhỏ," sư tử nói.'],
        ], "lion = sư tử\nmouse = con chuột\nangry = tức giận\nnet = cái lưới\nbites = cắn\nfree = tự do"],
        ['the-tortoise-and-the-hare', 'story', 'The Tortoise and the Hare', 'Rùa và thỏ', 'tortoise', '#CDEFD2', [
            ['The hare is fast. The tortoise is slow. "Let us have a race," says the tortoise.', 'Thỏ thì nhanh. Rùa thì chậm. "Chúng mình thi chạy nhé," rùa nói.'],
            ['The hare runs very fast. He looks back. The tortoise is far away.', 'Thỏ chạy rất nhanh. Nó nhìn lại phía sau. Rùa còn ở xa tít.'],
            ['"I have time," says the hare. He sleeps under a tree.', '"Mình còn nhiều thời gian," thỏ nói. Nó ngủ dưới một gốc cây.'],
            ['The tortoise walks and walks. He does not stop. He passes the sleeping hare.', 'Rùa cứ đi, đi mãi. Nó không dừng lại. Nó vượt qua chú thỏ đang ngủ.'],
            ['The hare wakes up and runs. But the tortoise wins the race!', 'Thỏ thức dậy và chạy. Nhưng rùa đã thắng cuộc đua!'],
        ], "hare = thỏ rừng\ntortoise = con rùa\nrace = cuộc đua\nfast = nhanh\nslow = chậm\nwins = thắng"],
        ['why-is-the-sky-blue', 'science', 'Why Is the Sky Blue?', 'Vì sao bầu trời màu xanh?', 'sky', '#CFE5F7', [
            ['Look up! The sky is blue. But why?', 'Hãy nhìn lên! Bầu trời màu xanh. Nhưng tại sao nhỉ?'],
            ['Light from the sun looks white. But it has many colors in it.', 'Ánh sáng từ mặt trời trông có màu trắng. Nhưng trong nó có rất nhiều màu.'],
            ['The air bounces the light around. Blue light bounces the most.', 'Không khí làm ánh sáng dội đi khắp nơi. Ánh sáng xanh dội đi nhiều nhất.'],
            ['So we see blue light all over the sky.', 'Vì thế chúng ta thấy ánh sáng xanh ở khắp bầu trời.'],
            ['At night, there is no sunlight. The sky is dark.', 'Vào ban đêm không có ánh nắng. Bầu trời tối đen.'],
        ], "light = ánh sáng\nair = không khí\nbounces = dội đi\nsunlight = ánh nắng\ndark = tối"],
        ['where-does-rain-come-from', 'science', 'Where Does Rain Come From?', 'Mưa đến từ đâu?', 'rain', '#D7EEF9', [
            ['The sun makes the water warm. The water goes up into the air.', 'Mặt trời làm nước ấm lên. Nước bay lên không khí.'],
            ['High in the sky, it is cold. The water makes clouds.', 'Ở trên cao, trời rất lạnh. Nước tạo thành những đám mây.'],
            ['The clouds get big and heavy. Then the water falls down. It is rain!', 'Những đám mây to dần và nặng dần. Rồi nước rơi xuống. Đó là mưa!'],
            ['The rain goes into rivers and the sea. Then it goes up again.', 'Mưa chảy vào sông và biển. Rồi nước lại bay lên.'],
        ], "warm = ấm\nclouds = những đám mây\nheavy = nặng\nfalls = rơi\nrivers = những dòng sông"],
    ];

    // Từ điển chung cho truyện (từ viết thường = nghĩa ngắn). Nghĩa riêng theo truyện nằm ở phần "từ mới" phía trên.
    $dict = [
        'a' => 'một', 'the' => '(chỉ người, vật đã nói tới)', 'is' => 'là, thì', 'are' => 'là, thì', 'he' => 'cậu ấy, nó', 'she' => 'cô ấy, nó',
        'it' => 'nó', 'its' => 'của nó', 'his' => 'của cậu ấy', 'her' => 'cô ấy', 'him' => 'cậu ấy, nó', 'i' => 'tớ, tôi', 'you' => 'bạn',
        'we' => 'chúng ta', 'us' => 'chúng ta', 'me' => 'tớ, tôi', 'in' => 'ở trong', 'on' => 'ở trên', 'under' => 'ở dưới', 'behind' => 'phía sau',
        'over' => 'qua, khắp', 'into' => 'vào trong', 'from' => 'từ', 'to' => 'tới, để', 'with' => 'với', 'at' => 'vào lúc', 'and' => 'và',
        'but' => 'nhưng', 'so' => 'vì thế', 'then' => 'rồi, sau đó', 'now' => 'bây giờ', 'not' => 'không', 'no' => 'không có',
        'does' => '(từ dùng để hỏi hoặc phủ định)', 'can' => 'có thể', 'cannot' => 'không thể', 'will' => 'sẽ', 'has' => 'có', 'have' => 'có',
        'let' => 'hãy để, cho', 'lets' => 'để cho', 'there' => 'ở đó, có', 'very' => 'rất', 'many' => 'nhiều', 'most' => 'nhiều nhất',
        'all' => 'tất cả, khắp', 'every' => 'mỗi', 'one' => 'một', 'next' => 'tiếp theo', 'up' => 'lên', 'down' => 'xuống', 'out' => 'ra ngoài',
        'back' => 'phía sau', 'away' => 'xa', 'far' => 'xa', 'around' => 'xung quanh', 'again' => 'lại, lần nữa', 'together' => 'cùng nhau',
        'why' => 'tại sao', 'where' => 'ở đâu', 'please' => 'làm ơn', 'thank' => 'cảm ơn', 'hello' => 'xin chào',
        'sun' => 'mặt trời', 'sky' => 'bầu trời', 'morning' => 'buổi sáng', 'birds' => 'những chú chim', 'bird' => 'chú chim', 'cloud' => 'đám mây',
        'clouds' => 'những đám mây', 'day' => 'ngày', 'way' => 'đường đi', 'home' => 'nhà', 'friends' => 'những người bạn', 'friend' => 'người bạn',
        'ball' => 'quả bóng', 'garden' => 'khu vườn', 'wall' => 'bức tường', 'dog' => 'con chó', 'mouth' => 'cái miệng', 'lion' => 'sư tử',
        'tree' => 'cái cây', 'mouse' => 'con chuột', 'nose' => 'cái mũi', 'net' => 'cái lưới', 'hare' => 'thỏ rừng', 'tortoise' => 'con rùa',
        'race' => 'cuộc đua', 'time' => 'thời gian', 'light' => 'ánh sáng', 'colors' => 'màu sắc', 'air' => 'không khí', 'night' => 'ban đêm',
        'sunlight' => 'ánh nắng', 'rain' => 'mưa', 'water' => 'nước', 'rivers' => 'những dòng sông', 'sea' => 'biển',
        'sunny' => 'Sunny (tên của mặt trời nhỏ)', 'tom' => 'Tom (tên một bạn nhỏ)',
        'little' => 'nhỏ bé', 'shy' => 'nhút nhát', 'small' => 'nhỏ', 'sad' => 'buồn', 'lost' => 'bị lạc', 'happy' => 'vui vẻ', 'red' => 'màu đỏ',
        'big' => 'to lớn', 'angry' => 'tức giận', 'free' => 'tự do', 'fast' => 'nhanh', 'slow' => 'chậm', 'sleeping' => 'đang ngủ',
        'blue' => 'màu xanh dương', 'white' => 'màu trắng', 'dark' => 'tối', 'warm' => 'ấm', 'high' => 'cao', 'cold' => 'lạnh', 'heavy' => 'nặng',
        'lives' => 'sống', 'say' => 'nói', 'says' => 'nói', 'hides' => 'trốn', 'wants' => 'muốn', 'help' => 'giúp đỡ', 'shines' => 'chiếu sáng',
        'plays' => 'chơi', 'play' => 'chơi', 'jumps' => 'nảy lên, nhảy', 'see' => 'nhìn thấy', 'comes' => 'đi tới', 'come' => 'đến',
        'sleeps' => 'ngủ', 'runs' => 'chạy', 'wakes' => 'thức dậy', 'go' => 'đi', 'goes' => 'đi', 'laughs' => 'cười', 'get' => 'trở nên, thoát',
        'hears' => 'nghe thấy', 'bites' => 'cắn', 'looks' => 'nhìn, trông', 'look' => 'nhìn', 'walks' => 'đi bộ', 'stop' => 'dừng lại',
        'passes' => 'vượt qua', 'wins' => 'thắng', 'bounces' => 'dội đi, nảy', 'makes' => 'làm cho, tạo ra', 'falls' => 'rơi',
    ];

    return ['topics' => $topics, 'stories' => $stories, 'dict' => $dict];
}

/** Nạp nội dung mẫu vào CSDL (chỉ gọi một lần từ migrate()). */
function sw_seed(PDO $pdo): void
{
    $d = sw_seed_data();
    $pdo->beginTransaction();
    try {
        $insT = $pdo->prepare('INSERT OR IGNORE INTO sw_topics (slug, title_en, title_vi, color, art, sort, active) VALUES (?,?,?,?,?,?,1)');
        $getT = $pdo->prepare('SELECT id FROM sw_topics WHERE slug = ?');
        $insC = $pdo->prepare('INSERT INTO sw_cards (topic_id, word, meaning, example, example_vi, art, sort, active) VALUES (?,?,?,?,?,?,?,1)');
        foreach ($d['topics'] as $i => [$slug, $en, $vi, $color, $art, $cards]) {
            $insT->execute([$slug, $en, $vi, $color, $art, ($i + 1) * 10]);
            if ($insT->rowCount() === 0) {
                continue; // chủ đề đã có → không nạp trùng thẻ
            }
            $getT->execute([$slug]);
            $tid = (int) $getT->fetchColumn();
            foreach ($cards as $j => [$w, $m, $ex, $exVi, $cArt]) {
                $insC->execute([$tid, $w, $m, $ex, $exVi, $cArt, ($j + 1) * 10]);
            }
        }
        $insS = $pdo->prepare('INSERT OR IGNORE INTO sw_stories (slug, kind, title, title_vi, art, color, pages, glossary, sort, active, created_at) VALUES (?,?,?,?,?,?,?,?,?,1,?)');
        foreach ($d['stories'] as $i => [$slug, $kind, $title, $titleVi, $art, $color, $pages, $gloss]) {
            $json = json_encode(array_map(fn($p) => ['en' => $p[0], 'vi' => $p[1]], $pages), JSON_UNESCAPED_UNICODE);
            $insS->execute([$slug, $kind, $title, $titleVi, $art, $color, $json, $gloss, ($i + 1) * 10, date('Y-m-d H:i:s')]);
        }
        $insD = $pdo->prepare('INSERT OR IGNORE INTO sw_dict (word, meaning) VALUES (?, ?)');
        foreach ($d['dict'] as $w => $m) {
            $insD->execute([$w, $m]);
        }
        $pdo->exec("INSERT OR REPLACE INTO settings (k, v) VALUES ('sw_seeded', '1')");
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
