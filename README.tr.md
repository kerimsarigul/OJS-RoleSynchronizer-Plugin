[Click here for the English README.](README.md)

# OJS 3.3 Rol Senkronizasyon Eklentisi

Bu OJS 3.3 eklentisi, aynı kurulumda bulunan farklı dergiler (bağlamlar) arasında kullanıcı rollerini senkronize etmenizi sağlar. Bir kaynak dergiden seçilen rolleri mevcut dergiye kopyalamak için kullanıcı dostu bir arayüz sunar, bu da çoklu dergi kurulumlarında kullanıcı yönetimini basitleştirir. **Bu eklenti, özellikle birden fazla dergi yöneten yayıncılar için faydalıdır, çünkü hakemlerin ve diğer kullanıcıların aynı rolleri birden fazla dergide üstlenme ihtiyacına çözüm sunar.** Bu sürüm, çok dilli destek ve yaygın web güvenlik açıklarına karşı geliştirilmiş güvenlik önlemleri içerir.

## Özellikler

-   Kullanıcı rollerini bir dergiden diğerine kopyalama.
-   Mevcut rolleri koruma; sadece eksik rolleri hedef dergideki kullanıcılara ekleme.
-   **Geliştirilmiş güvenlik önlemleri:**
    -   CSRF (Siteler Arası İstek Sahteciliği) koruması.
    -   SQL Enjeksiyon saldırılarını önlemek için güvenli sorgu yapısı.
-   Çok dilli destek (Türkçe ve İngilizce) ve çeviri anahtarı bulunmadığında yedek çeviri mekanizması.

<img width="828" height="648" alt="rolesynchronizerplugin_tr" src="https://github.com/user-attachments/assets/b5c93b47-b745-4b4f-9328-fd9fd431997d" />

## Kurulum

### Yöntem 1: Eklenti Galerisi Üzerinden (Önerilen)

1.  Eklentinin en son `.tar.gz` dosyasını bu deponun **[Releases](https://github.com/kerimsarigul/OJS-RoleSynchronizer-Plugin/releases)** (Sürümler) sayfasından indirin.

2.  OJS yönetici paneline giriş yapın.
3.  **Ayarlar > Web Sitesi > Eklentiler > Yeni Bir Eklenti Yükle** yolunu izleyin.
4.  İndirdiğiniz `roleSynchronizer.tar.gz` dosyasını yükleyin.
5.  Eklentiyi etkinleştirin.

### Yöntem 2: Manuel Kurulum (Kaynak Koddan)

1.  Kaynak kodu bu depodan klonlayın veya indirin.
2.  `roleSynchronizer/` klasörünü OJS kurulumunuzun `/plugins/generic/` dizinine yerleştirin.
3.  Dosya izinlerini kontrol edin (755 klasörler, 644 dosyalar).
4.  Eklentiyi yönetici panelinden etkinleştirin.

## Kullanım

1.  Rollerin kopyalanacağı (hedef) dergiye giriş yapın.
2.  **Ayarlar > Web Sitesi > Eklentiler > Genel Eklentiler** yolunu izleyin.
3.  **Rol Senkronizasyon** eklentisini bulun.
4.  **Ayarlar** butonuna tıklayın.
5.  Açılan pencerede kaynak dergiyi ve senkronize etmek istediğiniz rolleri seçin.
6.  **Senkronize Et** butonuna tıklayın.

## Önemli Notlar

-   ⚠️ **İşlem geri alınamaz!** Senkronizasyon işlemi veritabanında kalıcı değişiklikler yapar, bu nedenle mutlaka önceden veritabanı yedeği alın.
-   Mevcut roller değiştirilmez; sadece eksik olan roller hedef dergideki kullanıcılara eklenir.
-   Aynı kullanıcı birden fazla role sahip olabilir.
-   Sadece Dergi Yöneticisi ve Site Yöneticisi rolleri bu eklentiyi kullanabilir.

## Güvenlik İyileştirmeleri

-   **CSRF Koruması:** Senkronizasyon istekleri, OJS'nin yerleşik CSRF token mekanizması ile doğrulanarak yetkisiz istekler engellenir.
-   **SQL Enjeksiyonu Koruması:** Veritabanı sorgularında parametreli sorgu yapısı kullanılarak, kullanıcıdan gelen verilerin doğrudan sorguya dahil edilmesi önlenir.

## Teknik Detaylar

-   **Desteklenen OJS Sürümü**: 3.3.x
-   **Veritabanı Değişikliği**: Yok
-   **Dil Desteği**: Türkçe, İngilizce

## Sorun Giderme

### Eklenti yüklenmiyor
-   Dosya izinlerini kontrol edin.
-   OJS log dosyalarını kontrol edin.
-   Eklenti klasör yapısının doğru olduğunu kontrol edin.

### Senkronizasyon çalışmıyor
-   Kaynak ve hedef dergilerde aynı rollerin varlığını kontrol edin.
-   PHP hata kayıtlarını kontrol edin.
-   Kullanıcının yeterli yetkiye sahip olup olmadığını kontrol edin.

### CSRF hatası
-   Sayfayı yenileyin ve tekrar deneyin.
-   Tarayıcı önbelleğini ve çerezleri temizleyin.

## Destek

Eklenti ile ilgili sorunlar, hata raporları veya özellik önerileri için lütfen aşağıdaki adresten iletişime geçin:

-   **Geliştiren:** Kerim SARIGÜL
-   **E-posta:** kerim@kerimsarigul.com
-   **GitHub:** [OJS-RoleSynchronizer-Plugin](https://github.com/kerimsarigul/OJS-RoleSynchronizer-Plugin/)

## Lisans

Bu eklenti GPL v3 lisansı altında dağıtılmaktadır.
