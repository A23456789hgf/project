function loadPDFLibraries() {
    return new Promise((resolve, reject) => {
        if (window.jspdf && window.html2canvas) {
            resolve();
            return;
        }

        //   jsPDFمكتبة
        if (!window.jspdf) {
            const script1 = document.createElement('script');
            script1.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
            script1.onload = () => {
                //   html2canvas  مكتبة
                if (!window.html2canvas) {
                    const script2 = document.createElement('script');
                    script2.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                    script2.onload = resolve;
                    script2.onerror = reject;
                    document.head.appendChild(script2);
                } else {
                    resolve();
                }
            };
            script1.onerror = reject;
            document.head.appendChild(script1);
        } else {
            resolve();
        }
    });
}

async function exportProductsToPDF(products, selectedOnly = false) {
    try {
        if (products.length === 0) {
            alert(' لا توجد منتجات للتصدير');
            return;
        }

        console.log(` جاري تصدير ${products.length} منتج إلى PDF...`);
        showLoading();

        // تحميل المكتبات أولاً
        await loadPDFLibraries();

        // إنشاء محتوى HTML مع تحسين الصور
        const htmlContent = selectedOnly ?
            await generateProductCardsContent(products) : // استخدام await هنا
            generatePDFTableContent(products, false);

        // إنشاء عنصر مؤقت مخفي
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = htmlContent;
        tempDiv.style.width = '210mm';
        tempDiv.style.background = 'white';
        tempDiv.style.padding = '0';
        tempDiv.style.position = 'fixed';
        tempDiv.style.left = '-9999px';
        tempDiv.style.top = '-9999px';
        tempDiv.style.zIndex = '-1';
        tempDiv.style.overflow = 'hidden';
        document.body.appendChild(tempDiv);

        // معالجة الصور لتحميلها بشكل صحيح
        await preloadImages(tempDiv);

        // الانتظار لتحميل المحتوى
        await new Promise(resolve => setTimeout(resolve, 100));

        // استخدام html2canvas   إعدادات محسنة
        const canvas = await html2canvas(tempDiv, {
            scale: 2,
            useCORS: true,
            allowTaint: true,
            logging: true,
            backgroundColor: '#ffffff',
            width: tempDiv.scrollWidth,
            height: tempDiv.scrollHeight,
            scrollX: 0,
            scrollY: 0,
            onclone: function (clonedDoc) {
                // تأكد من أن المحتوى معروض بشكل صحيح في النسخة المستنسخة
                const clonedDiv = clonedDoc.querySelector('div');
                if (clonedDiv) {
                    clonedDiv.style.width = '210mm';
                    clonedDiv.style.overflow = 'visible';
                }

                // إضافة crossOrigin لجميع الصور في المستنسخ
                const images = clonedDoc.querySelectorAll('img');
                images.forEach(img => {
                    img.crossOrigin = 'anonymous';
                });
            }
        });

        // تحويل Canvas إلى PDF باستخدام jsPDF
        const {
            jsPDF
        } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');

        const imgWidth = 210;
        const pageHeight = 297;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        let heightLeft = imgHeight;
        let position = 0;

        // الصفحة الأولى
        pdf.addImage(canvas, 'JPEG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;

        // صفحات إضافية إذا كان المحتوى طويل
        while (heightLeft > 0) {
            position = heightLeft - imgHeight;
            pdf.addPage();
            pdf.addImage(canvas, 'JPEG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
        }

        // حفظ الملف
        pdf.save(`تقرير-المنتجات-${Date.now()}.pdf`);

        // تنظيف
        document.body.removeChild(tempDiv);
        hideLoading();

        console.log(' تم تحميل PDF بنجاح');

    } catch (error) {
        console.error(' خطأ في تصدير PDF:', error);
        hideLoading();

        // تنظيف في حالة الخطأ
        const tempDiv = document.querySelector('div');
        if (tempDiv && tempDiv.innerHTML.includes('نظام إدارة المبيعات')) {
            document.body.removeChild(tempDiv);
        }

        alert(' حدث خطأ أثناء تصدير PDF: ' + error.message);
    }
}

// دالة لتحميل الصور مسبقاً
async function preloadImages(container) {
    const images = container.getElementsByTagName('img');
    const promises = [];

    for (let img of images) {
        const promise = new Promise(async (resolve) => {
            // إذا كانت الصورة base64 صالحة، تحميلها
            if (img.src.startsWith('data:image/') && img.src.length > 100) {
                img.onload = resolve;
                img.onerror = () => {
                    console.warn(' فشل تحميل الصورة base64');
                    img.src = getDefaultProductImage();
                    resolve();
                };
            } else if (img.src.startsWith('http')) {
                // محاولة تحويل صورة السيرفر إلى base64
                try {
                    const base64Image = await convertImageToBase64(img.src);
                    img.src = base64Image;
                    resolve();
                } catch (error) {
                    console.warn(' فشل تحميل صورة المنتج:', img.src);
                    img.src = getDefaultProductImage();
                    resolve();
                }
            } else {
                // صورة غير صالحة، استخدام البديل
                img.src = getDefaultProductImage();
                resolve();
            }
        });
        promises.push(promise);
    }

    return Promise.all(promises);
}

// دالة جديدة لتحويل الصور إلى Base64
function convertImageToBase64(url) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'Anonymous';

        img.onload = function () {
            try {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);
                const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                resolve(dataUrl);
            } catch (error) {
                reject(error);
            }
        };

        img.onerror = function () {
            reject(new Error('فشل تحميل الصورة'));
        };

        // إضافة طابع زمني لمنع التخزين المؤقت
        const timestamp = new Date().getTime();
        const urlWithTimestamp = url + (url.includes('?') ? '&' : '?') + 't=' + timestamp;

        img.src = urlWithTimestamp;

        // وقت انتظار أقصى 5 ثوانٍ
        setTimeout(() => {
            if (!img.complete) {
                reject(new Error('انتهت مهلة تحميل الصورة'));
            }
        }, 5000);
    });
}

// دالة للحصول على صورة افتراضية صالحة
function getDefaultProductImage() {
    const svgString = `
        <svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
            <rect width="100%" height="100%" fill="#f8f9fa"/>
            <circle cx="100" cy="80" r="30" fill="#e9ecef" stroke="#6c757d" stroke-width="1"/>
            <rect x="70" y="120" width="60" height="30" rx="3" fill="#e9ecef" stroke="#6c757d" stroke-width="1"/>
            <text x="100" y="140" font-family="Arial" font-size="12" fill="#6c757d" text-anchor="middle">لا توجد صورة</text>
        </svg>
    `;

    // تحويل SVG إلى Base64 بشكل آمن
    try {
        return 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgString)));
    } catch (error) {
        // بديل بسيط إذا فشل الترميز
        return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjhmOWZhIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPu+Un++Un++Un++Un++Un++Un++Un++Un++Un++Un++UnzwvdGV4dD48L3N2Zz4=';
    }
}

// ========== دالة إنشاء محتوى PDF بتنسيق البطاقات (محدثة) ==========
async function generateProductCardsContent(products) {
    const totalQty = products.reduce((sum, p) => sum + (parseInt(p.qty) || 0), 0);
    const totalValue = products.reduce((sum, p) => sum + ((parseFloat(p.price) || 0) * (parseInt(p.qty) || 0)), 0);

    // معالجة الصور أولاً
    const productImages = {};
    for (const product of products) {
        try {
            const imageUrl = await getProductImageUrl(product);
            productImages[product.id || product.name] = imageUrl;
        } catch (error) {
            console.warn(` فشل تحميل صورة المنتج ${product.name}:`, error);
            productImages[product.id || product.name] = getDefaultProductImage();
        }
    }

    // صفحة الغلاف (واحدة فقط)
    let cardsHTML = `
        <div style="font-family: Arial, sans-serif; direction: rtl; width: 210mm; background: white; padding: 20mm; min-height: 297mm; box-sizing: border-box; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
            <!-- رأس التقرير -->
            <div style="border-bottom: 3px solid #2c5aa0; padding-bottom: 30px; margin-bottom: 40px; width: 100%;">
                <h1 style="color: #2c5aa0; font-size: 32px; margin: 0 0 15px 0; font-weight: bold;"> نظام إدارة المبيعات</h1>
                <h2 style="color: #4a9eff; font-size: 24px; margin: 15px 0;"> بطاقات المنتجات المحددة</h2>
                <div style="margin-top: 20px;">
                    <span style="background: #e3f2fd; padding: 10px 20px; border-radius: 20px; display: inline-block; margin: 5px; font-size: 14px; color: #2c5aa0;">
                         ${new Date().toLocaleDateString('ar-EG')}
                    </span>
                    <span style="background: #e3f2fd; padding: 10px 20px; border-radius: 20px; display: inline-block; margin: 5px; font-size: 14px; color: #2c5aa0;">
                         ${new Date().toLocaleTimeString('ar-EG')}
                    </span>
                </div>
            </div>
            
            <!-- ملخص سريع -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; text-align: center; width: 80%; margin-bottom: 40px;">
                <h3 style="margin: 0 0 25px 0; font-size: 22px;"> ملخص المنتجات المحددة</h3>
                <div style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 25px;">
                    <div style="min-width: 120px;">
                        <div style="font-size: 32px; font-weight: bold;">${products.length}</div>
                        <div style="font-size: 16px; opacity: 0.9;">عدد المنتجات</div>
                    </div>
                    <div style="min-width: 120px;">
                        <div style="font-size: 32px; font-weight: bold;">${totalQty}</div>
                        <div style="font-size: 16px; opacity: 0.9;">إجمالي الكمية</div>
                    </div>
                    <div style="min-width: 120px;">
                        <div style="font-size: 32px; font-weight: bold;">${formatPrice(totalValue)}</div>
                        <div style="font-size: 16px; opacity: 0.9;">إجمالي القيمة (د.م)</div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // بطاقات المنتجات (صفحة منفصلة لكل منتج)
    products.forEach((product, index) => {
        const productImage = productImages[product.id || product.name] || getDefaultProductImage();

        cardsHTML += `
            <div style="page-break-before: always; font-family: Arial, sans-serif; direction: rtl; width: 210mm; background: white; padding: 15mm; min-height: 297mm; box-sizing: border-box;">
                <!-- بطاقة منتج ${index + 1} -->
                <div style="border: 2px solid #030303ff; border-radius: 12px; overflow: hidden; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1); height: 100%; display: flex; flex-direction: column;">
                    
                    <!-- رأس البطاقة -->
                    <div style="background: linear-gradient(135deg, #dee2e6b6 0%, #dee2e994 100%); color: #2c5aa0; padding: 25px; text-align: center;">
                        <h2 style="margin: 0; font-size: 26px; font-weight: bold;">${escapeHTML(product.name || 'منتج غير محدد')}</h2>
                        <p style="margin: 10px 0 0 0; font-size: 18px; opacity: 0.9;">SKU: ${escapeHTML(product.sku || 'غير محدد')}</p>
                    </div>
                    
                    <!-- محتوى البطاقة -->
                    <div style="padding: 30px; flex: 1; display: flex; flex-direction: column; gap: 25px;">
                        
                        <!-- صورة المنتج -->
                        <div style="text-align: center; background: #f8f9fa; padding: 25px; border-radius: 8px;">
                            <div style="width: 200px; height: 200px; margin: 0 auto; border: 2px solid #dee2e6; border-radius: 8px; overflow: hidden; background: white;">
                                <img src="${productImage}" 
                                    alt="${escapeHTML(product.name || 'منتج')}" 
                                    style="width: 100%; height: 100%; object-fit: contain; display: block;" 
                                    crossOrigin="anonymous">
                            </div>
                            <p style="margin: 10px 0 0 0; color: #666; font-size: 12px;"> صورة المنتج</p>
                        </div>
                        
                        <!-- معلومات المنتج -->
                        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                            <tbody>
                                <tr style="background: #f8f9fa;">
                                    <td style="padding: 12px; border: 1px solid #dee2e6; font-weight: bold; width: 35%; color: #2c5aa0;"> الفئة</td>
                                    <td style="padding: 12px; border: 1px solid #dee2e6;">${escapeHTML(product.category || 'غير محدد')}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px; border: 1px solid #dee2e6; font-weight: bold; color: #2c5aa0;"> العلامة التجارية</td>
                                    <td style="padding: 12px; border: 1px solid #dee2e6;">${escapeHTML(product.brand || 'غير محدد')}</td>
                                </tr>
                                <tr style="background: #f8f9fa;">
                                    <td style="padding: 12px; border: 1px solid #dee2e6; font-weight: bold; color: #2c5aa0;"> الوحدة</td>
                                    <td style="padding: 12px; border: 1px solid #dee2e6;">${escapeHTML(product.unit || 'غير محدد')}</td>
                                </tr>
                                <tr >
                                    <td style="padding: 12px; border: 1px solid #dee2e6; font-weight: bold; color: #2c5aa0;"> السعر</td>
                                    <td style="padding: 12px; border: 1px solid #dee2e6; font-size: 18px; font-weight: bold; color: #2c5aa0;">${formatPrice(product.price)} د.م</td>
                                </tr>
                                <tr style="background: #f8f9fa;">
                                    <td style="padding: 12px; border: 1px solid #dee2e6; font-weight: bold; color: #2c5aa0;"> الكمية المتاحة</td>
                                    <td style="padding: 12px; border: 1px solid #dee2e6; font-size: 18px; font-weight: bold; color: #2c5aa0;">${escapeHTML(product.qty || '0')} وحدة</td>
                                </tr>
                                <tr ">
                                    <td style="padding: 12px; border: 1px solid #dee2e6; font-weight: bold; color: #2c5aa0;"> القيمة الإجمالية</td>
                                    <td style="padding: 12px; border: 1px solid #dee2e6; font-size: 18px; font-weight: bold; color: #2c5aa0;">${formatPrice((parseFloat(product.price) || 0) * (parseInt(product.qty) || 0))} د.م</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        ${product.description ? `
                            <div style="padding: 20px; background: #f8f9fa; border-right: 4px solid #2c5aa0; border-radius: 6px;">
                                <strong style="color: #2c5aa0; display: block; margin-bottom: 10px; font-size: 16px;"> الوصف:</strong>
                                <p style="margin: 0; color: #495057; line-height: 1.6; font-size: 14px;">${escapeHTML(product.description)}</p>
                            </div>
                        ` : ''}
                    </div>
                    
                    <!-- تذييل البطاقة -->
                    <div style="background: #f8f9fa; padding: 20px; border-top: 1px solid #dee2e6; text-align: center; font-size: 14px; color: #6c757d;">
                        <span>بطاقة منتج رقم ${index + 1} من ${products.length} | ${new Date().toLocaleDateString('ar-EG')}</span>
                    </div>
                </div>
            </div>
        `;
    });

    return cardsHTML;
}

// دالة مساعدة جديدة للحصول على رابط صورة المنتج
async function getProductImageUrl(product) {
    if (!product.image && !product.image_url) {
        return getDefaultProductImage();
    }

    const imageUrl = product.image || product.image_url;

    // إذا كانت الصورة بالفعل base64
    if (imageUrl.startsWith('data:image/')) {
        return imageUrl;
    }

    // إذا كانت صورة من السيرفر، حاول تحويلها إلى base64
    try {
        return await convertImageToBase64(imageUrl);
    } catch (error) {
        console.warn(' فشل تحويل الصورة إلى base64:', error);
        return getDefaultProductImage();
    }
}

// ========== دالة إنشاء محتوى PDF بتنسيق الجدول (تبقى كما هي) ==========
function generatePDFTableContent(products, selectedOnly) {
    const totalQty = products.reduce((sum, p) => sum + (parseInt(p.qty) || 0), 0);
    const totalValue = products.reduce((sum, p) => sum + (parseFloat(p.price) || 0), 0);

    // تقسيم المنتجات إلى صفحات
    const productsPerPage = 15;
    const pages = [];

    for (let i = 0; i < products.length; i += productsPerPage) {
        pages.push(products.slice(i, i + productsPerPage));
    }

    let tablesHTML = '';

    pages.forEach((pageProducts, pageIndex) => {
        const isFirstPage = pageIndex === 0;
        const isLastPage = pageIndex === pages.length - 1;

        tablesHTML += `
            <div style="${!isFirstPage ? 'page-break-before: always;' : ''} font-family: Arial, sans-serif; direction: rtl; width: 210mm; background: white; padding: 15mm; min-height: 297mm; box-sizing: border-box;">
                ${isFirstPage ? `
                    <!-- الرأس للصفحة الأولى فقط -->
                    <div style="text-align: center; margin-bottom: 20px; border-bottom: 3px solid #2c5aa0; padding-bottom: 15px;">
                        <h2 style="color: #2c5aa0; font-size: 28px; margin: 0 0 10px 0;">نظام إدارة المبيعات</h2>
                        <h1 style="color: #2c5aa0; font-size: 24px; margin: 10px 0;">تقرير المنتجات</h1>
                        <p style="color: #666; font-size: 14px; margin: 5px 0;">التاريخ: ${new Date().toLocaleDateString('ar-EG')}</p>
                        <p style="color: #666; font-size: 14px; margin: 5px 0;">الوقت: ${new Date().toLocaleTimeString('ar-EG')}</p>
                    </div>
                    
                    <!-- الملخص للصفحة الأولى فقط -->
                    <div style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <h3 style="color: #2c5aa0; font-size: 18px; margin: 0 0 15px 0;">ملخص التقرير</h3>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
                            <div style="background: white; padding: 10px; border: 1px solid #e9ecef; text-align: center; border-radius: 5px;">
                                <strong style="display: block; color: #495057; margin-bottom: 5px;">عدد المنتجات</strong>
                                <span style="color: #6c757d;">${products.length} منتج</span>
                            </div>
                            <div style="background: white; padding: 10px; border: 1px solid #e9ecef; text-align: center; border-radius: 5px;">
                                <strong style="display: block; color: #495057; margin-bottom: 5px;">نوع التصدير</strong>
                                <span style="color: #6c757d;">${selectedOnly ? 'منتجات محددة' : 'جميع المنتجات'}</span>
                            </div>
                            <div style="background: white; padding: 10px; border: 1px solid #e9ecef; text-align: center; border-radius: 5px;">
                                <strong style="display: block; color: #495057; margin-bottom: 5px;">إجمالي الكمية</strong>
                                <span style="color: #6c757d;">${totalQty} وحدة</span>
                            </div>
                            <div style="background: white; padding: 10px; border: 1px solid #e9ecef; text-align: center; border-radius: 5px;">
                                <strong style="display: block; color: #495057; margin-bottom: 5px;">إجمالي القيمة</strong>
                                <span style="color: #6c757d;">${totalValue.toFixed(2)} د.م</span>
                            </div>
                        </div>
                    </div>
                ` : ''}
                
                <!-- الجدول -->
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #dee2e6; font-size: 10px; margin-bottom: 20px;">
                    <thead>
                        <tr style="background: #2c5aa0;">
                            <th style="border: 1px solid #dee2e6; padding: 8px 6px; color: white; text-align: center; width: 40px;">#</th>
                            <th style="border: 1px solid #dee2e6; padding: 8px 6px; color: white; text-align: right;">اسم المنتج</th>
                            <th style="border: 1px solid #dee2e6; padding: 8px 6px; color: white; text-align: right;">SKU</th>
                            <th style="border: 1px solid #dee2e6; padding: 8px 6px; color: white; text-align: right;">الفئة</th>
                            <th style="border: 1px solid #dee2e6; padding: 8px 6px; color: white; text-align: center; width: 70px;">السعر</th>
                            <th style="border: 1px solid #dee2e6; padding: 8px 6px; color: white; text-align: center; width: 60px;">الكمية</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${pageProducts.map((product, index) => {
            const globalIndex = pageIndex * productsPerPage + index;
            return `
                                <tr style="background: ${globalIndex % 2 === 0 ? '#ffffff' : '#f8f9fa'};">
                                    <td style="border: 1px solid #dee2e6; padding: 6px; text-align: center;"><strong>${globalIndex + 1}</strong></td>
                                    <td style="border: 1px solid #dee2e6; padding: 6px; text-align: right;">${escapeHTML(product.name || 'غير محدد')}</td>
                                    <td style="border: 1px solid #dee2e6; padding: 6px; text-align: right;">${escapeHTML(product.sku || 'غير محدد')}</td>
                                    <td style="border: 1px solid #dee2e6; padding: 6px; text-align: right;">${escapeHTML(product.category || 'غير محدد')}</td>
                                    <td style="border: 1px solid #dee2e6; padding: 6px; text-align: center;"><strong>${formatPrice(product.price)} د.م</strong></td>
                                    <td style="border: 1px solid #dee2e6; padding: 6px; text-align: center;">${escapeHTML(product.qty || '0')}</td>
                                </tr>
                            `;
        }).join('')}
                    </tbody>
                </table>
                
                ${isLastPage ? `
                    <!-- التذييل للصفحة الأخيرة فقط -->
                    <div style="text-align: center; font-size: 11px; color: #666; border-top: 1px solid #dee2e6; padding-top: 15px;">
                        <p style="margin: 5px 0;">تم إنشاء هذا التقرير بواسطة نظام إدارة المبيعات</p>
                        <p style="margin: 5px 0;">© ${new Date().getFullYear()} - جميع الحقوق محفوظة</p>
                    </div>
                ` : `
                    <!-- ترقيم الصفحات -->
                    <div style="text-align: center; font-size: 11px; color: #666;">
                        صفحة ${pageIndex + 1} من ${pages.length}
                    </div>
                `}
            </div>
        `;
    });

    return tablesHTML;
}

// ========== دالة تصدير جدول الأهداف الخاصة إلى PDF ==========
async function exportObjectivesToPDF() {
    try {
        const table = document.getElementById('specialObjectivesTable');
        if (!table) {
            alert(' جدول الأهداف الخاصة غير موجود');
            return;
        }

        const rows = table.querySelectorAll('tbody tr.objective-row');
        if (rows.length === 0) {
            alert(' لا توجد أهداف خاصة للتصدير');
            return;
        }

        console.log(` جاري تصدير ${rows.length} هدف خاص إلى PDF...`);
        showLoading();

        // تحميل المكتبات
        await loadPDFLibraries();

        // استخراج بيانات الأهداف
        const objectives = [];
        rows.forEach((row) => {
            const cells = row.querySelectorAll('td');
            objectives.push({
                objective: cells[0]?.querySelector('input')?.value || '',
                weight: cells[1]?.querySelector('input')?.value || '',
                targetValue: cells[2]?.querySelector('input')?.value || '',
                unit: cells[3]?.querySelector('input')?.value || ''
            });
        });

        // إنشاء محتوى HTML للـ PDF
        const htmlContent = `
            <div style="font-family: Arial, sans-serif; direction: rtl; width: 210mm; background: white; padding: 15mm; min-height: 297mm; box-sizing: border-box;">
                <!-- الرأس -->
                <div style="text-align: center; margin-bottom: 20px; border-bottom: 3px solid #2c5aa0; padding-bottom: 15px;">
                    <h2 style="color: #2c5aa0; font-size: 28px; margin: 0 0 10px 0;">نظام إدارة المشاريع</h2>
                    <h1 style="color: #2c5aa0; font-size: 24px; margin: 10px 0;">تقرير الأهداف الخاصة</h1>
                    <p style="color: #666; font-size: 14px; margin: 5px 0;">التاريخ: ${new Date().toLocaleDateString('ar-EG')}</p>
                    <p style="color: #666; font-size: 14px; margin: 5px 0;">الوقت: ${new Date().toLocaleTimeString('ar-EG')}</p>
                </div>

                <!-- الملخص -->
                <div style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <h3 style="color: #2c5aa0; font-size: 18px; margin: 0 0 15px 0;">ملخص التقرير</h3>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                        <div style="background: white; padding: 10px; border: 1px solid #e9ecef; text-align: center; border-radius: 5px;">
                            <strong style="display: block; color: #495057; margin-bottom: 5px;">عدد الأهداف</strong>
                            <span style="color: #6c757d; font-size: 18px;">${objectives.length} هدف</span>
                        </div>
                        <div style="background: white; padding: 10px; border: 1px solid #e9ecef; text-align: center; border-radius: 5px;">
                            <strong style="display: block; color: #495057; margin-bottom: 5px;">إجمالي الأوزان</strong>
                            <span style="color: #6c757d; font-size: 18px;">${objectives.reduce((sum, obj) => sum + (parseFloat(obj.weight) || 0), 0).toFixed(2)}%</span>
                        </div>
                    </div>
                </div>

                <!-- الجدول -->
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #dee2e6; font-size: 12px;">
                    <thead>
                        <tr style="background: #2c5aa0;">
                            <th style="border: 1px solid #dee2e6; padding: 10px 6px; color: white; text-align: right; width: 40px;">#</th>
                            <th style="border: 1px solid #dee2e6; padding: 10px 6px; color: white; text-align: right;">الهدف الخاص</th>
                            <th style="border: 1px solid #dee2e6; padding: 10px 6px; color: white; text-align: center; width: 80px;">وزن الهدف (%)</th>
                            <th style="border: 1px solid #dee2e6; padding: 10px 6px; color: white; text-align: center; width: 80px;">القيمة المستهدفة</th>
                            <th style="border: 1px solid #dee2e6; padding: 10px 6px; color: white; text-align: right; width: 80px;">وحدة القياس</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${objectives.map((obj, index) => `
                            <tr style="background: ${index % 2 === 0 ? '#ffffff' : '#f8f9fa'};">
                                <td style="border: 1px solid #dee2e6; padding: 8px 6px; text-align: center; font-weight: bold;">${index + 1}</td>
                                <td style="border: 1px solid #dee2e6; padding: 8px 6px; text-align: right;">${escapeHTML(obj.objective)}</td>
                                <td style="border: 1px solid #dee2e6; padding: 8px 6px; text-align: center;"><strong>${obj.weight}</strong></td>
                                <td style="border: 1px solid #dee2e6; padding: 8px 6px; text-align: center;">${obj.targetValue}</td>
                                <td style="border: 1px solid #dee2e6; padding: 8px 6px; text-align: right;">${escapeHTML(obj.unit)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>

                <!-- التذييل -->
                <div style="text-align: center; font-size: 11px; color: #666; border-top: 1px solid #dee2e6; padding-top: 15px; margin-top: 20px;">
                    <p style="margin: 5px 0;">تم إنشاء هذا التقرير بواسطة نظام إدارة المشاريع</p>
                    <p style="margin: 5px 0;">© ${new Date().getFullYear()} - جميع الحقوق محفوظة</p>
                </div>
            </div>
        `;

        // إنشاء عنصر مؤقت
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = htmlContent;
        tempDiv.style.width = '210mm';
        tempDiv.style.background = 'white';
        tempDiv.style.padding = '0';
        tempDiv.style.position = 'fixed';
        tempDiv.style.left = '-9999px';
        tempDiv.style.top = '-9999px';
        tempDiv.style.zIndex = '-1';
        document.body.appendChild(tempDiv);

        // الانتظار لتحميل المحتوى
        await new Promise(resolve => setTimeout(resolve, 100));

        // تحويل إلى Canvas
        const canvas = await html2canvas(tempDiv, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff',
            width: tempDiv.scrollWidth,
            height: tempDiv.scrollHeight,
            scrollX: 0,
            scrollY: 0
        });

        // تحويل إلى PDF
        const {
            jsPDF
        } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');

        const imgWidth = 210;
        const pageHeight = 297;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        let heightLeft = imgHeight;
        let position = 0;

        pdf.addImage(canvas, 'JPEG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;

        while (heightLeft > 0) {
            position = heightLeft - imgHeight;
            pdf.addPage();
            pdf.addImage(canvas, 'JPEG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
        }

        // حفظ الملف
        pdf.save(`تقرير-الأهداف-الخاصة-${Date.now()}.pdf`);

        // تنظيف
        document.body.removeChild(tempDiv);
        hideLoading();

        console.log(' تم تصدير الأهداف إلى PDF بنجاح');

    } catch (error) {
        console.error(' خطأ في تصدير الأهداف إلى PDF:', error);
        hideLoading();
        alert(' حدث خطأ أثناء تصدير الأهداف إلى PDF: ' + error.message);
    }
}

// دوال مساعدة
function escapeHTML(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatPrice(price) {
    if (!price) return '0.00';
    const num = parseFloat(price);
    return isNaN(num) ? '0.00' : num.toFixed(2);
}

function showLoading() {
    const loading = document.createElement('div');
    loading.id = 'pdf-loading-overlay';
    loading.innerHTML = `
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 9999; display: flex; align-items: center; justify-content: center;">
            <div style="background: white; padding: 30px; border-radius: 10px; text-align: center; min-width: 300px;">
                <div style="font-size: 18px; color: #2c5aa0; margin-bottom: 15px;"> جاري إنشاء ملف PDF...</div>
                <div style="font-size: 14px; color: #666; margin-bottom: 15px;">يرجى الانتظار...</div>
                <div style="width: 100%; height: 4px; background: #e0e0e0; border-radius: 2px; overflow: hidden;">
                    <div style="width: 100%; height: 100%; background: linear-gradient(90deg, #2c5aa0, #4a9eff); animation: loading 1.5s infinite;"></div>
                </div>
            </div>
        </div>
        <style>
            @keyframes loading {
                0% { transform: translateX(-100%); }
                100% { transform: translateX(100%); }
            }
        </style>
    `;
    document.body.appendChild(loading);
}

function hideLoading() {
    const loading = document.getElementById('pdf-loading-overlay');
    if (loading) {
        document.body.removeChild(loading);
    }
}

// ========== دالة تصدير المشروع إلى PDF (جديد) ==========
async function exportProjectToPDF(project, logoUrl) {
    try {
        if (!project) {
            alert('لا توجد بيانات للمشروع');
            return;
        }

        console.log(`جاري تصدير المشروع: ${project.project_name}`);
        showLoading();

        await loadPDFLibraries();

        const htmlContent = generateProjectContent(project, logoUrl);

        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = htmlContent;
        Object.assign(tempDiv.style, {
            width: '210mm',
            background: 'white',
            padding: '0',
            position: 'fixed',
            left: '-9999px',
            top: '-9999px',
            zIndex: '-1',
            overflow: 'hidden'
        });
        document.body.appendChild(tempDiv);

        await preloadImages(tempDiv);
        await new Promise(resolve => setTimeout(resolve, 500));

        const canvas = await html2canvas(tempDiv, {
            scale: 2,
            useCORS: true,
            allowTaint: true,
            logging: true,
            backgroundColor: '#ffffff',
            width: tempDiv.scrollWidth,
            height: tempDiv.scrollHeight,
            windowWidth: tempDiv.scrollWidth,
            windowHeight: tempDiv.scrollHeight
        });

        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');
        const imgWidth = 210;
        const pageHeight = 297;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        let heightLeft = imgHeight;
        let position = 0;

        pdf.addImage(canvas, 'JPEG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;

        while (heightLeft > 0) {
            position = heightLeft - imgHeight;
            pdf.addPage();
            pdf.addImage(canvas, 'JPEG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
        }

        pdf.save(`وثيقة-مشروع-${project.form_number || Date.now()}.pdf`);

        document.body.removeChild(tempDiv);
        hideLoading();

    } catch (error) {
        console.error('خطأ في تصدير المشروع:', error);
        hideLoading();
        alert('حدث خطأ: ' + error.message);
    }
}

function generateProjectContent(project, logoUrl) {
    const css = `
        :root { --primary: #0C5B47; --primary-dark: #084234; --secondary: #1E3A8A; --accent: #B19446; --bg-light: #F8FAFC; --border: #E2E8F0; --text-dark: #1E293B; --text-muted: #64748B; --radius-lg: 15px; --radius-md: 8px; --shadow: 0 4px 15px rgba(0, 0, 0, 0.05); --danger: #DC2626; --success: #059669; --warning: #D97706; }
        body { font-family: 'Cairo', sans-serif; background-color: #fff; color: #1E293B; margin: 0; padding: 0; line-height: 1.6; width: 210mm; font-size: 10pt; direction: rtl; }
        .page { width: 210mm; min-height: 297mm; padding: 20mm 15mm; position: relative; background: #fff; box-sizing: border-box; overflow: hidden; page-break-after: always; }
        .cover-page { display: flex; flex-direction: column; justify-content: space-between; background: linear-gradient(135deg, #fdfdfd 0%, #f4f7f6 100%); border: 15px solid #0C5B47; outline: 2px solid #B19446; outline-offset: -10px; height: 297mm; }
        .cover-header { text-align: center; margin-bottom: 30px; }
        .cover-header h1 { font-size: 24pt; color: #0C5B47; margin: 10px 0; font-weight: 900; }
        .project-identity { text-align: center; padding: 30px 20px; background: white; border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border-top: 6px solid #B19446; margin: 20px 0; flex-grow: 1; }
        .project-identity .doc-label { color: #B19446; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; font-size: 12pt; margin-bottom: 10px; display: block; }
        .project-identity h3 { font-size: 28pt; color: #0C5B47; margin: 15px 0; line-height: 1.3; padding: 0 10px; }
        .cover-footer-meta { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #E2E8F0; }
        .page-header { position: absolute; top: 15mm; left: 15mm; right: 15mm; border-bottom: 2px solid #0C5B47; padding-bottom: 8px; display: flex; justify-content: space-between; align-items: center; background: white; z-index: 100; }
        .section-title { color: #0C5B47; border-bottom: 2px solid #B19446; padding-bottom: 8px; margin: 20px 0 15px; font-size: 14pt; font-weight: 800; }
        .section-title .idx { background: #B19446; color: white; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; margin-left: 10px; font-size: 10pt; }
        .card { background: white; border: 1px solid #E2E8F0; border-radius: 8px; padding: 15px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
        .card-head { background: linear-gradient(to left, #0C5B47, #084234); color: white; padding: 10px 15px; margin: -15px -15px 15px -15px; border-radius: 8px 8px 0 0; font-weight: 700; display: flex; align-items: center; gap: 10px; font-size: 11pt; }
        .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 10px; }
        .item { border-bottom: 1px dashed #E2E8F0; padding-bottom: 10px; }
        .label { font-weight: 600; color: #084234; font-size: 10pt; margin-bottom: 5px; }
        .value { color: #1E293B; font-size: 11pt; }
        .value.money { color: #059669; font-weight: 700; }
        .badge { padding: 5px 12px; border-radius: 50px; font-size: 9pt; font-weight: 700; display: inline-block; }
        .b-success { background: #DCFCE7; color: #166534; }
        .b-warning { background: #FEF9C3; color: #854d0e; }
        .b-primary { background: #DBEAFE; color: #1e40af; }
        table.t { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 9.5pt; }
        table.t th { background: #0C5B47; color: white; padding: 10px 12px; text-align: center; font-weight: 600; border: 1px solid #084234; font-size: 10pt; }
        table.t td { padding: 8px 12px; border: 1px solid #E2E8F0; text-align: center; font-size: 10pt; }
        table.t tr:nth-child(even) { background: #f9fbfb; }
        .narrative { border: 1px solid #E2E8F0; border-radius: 8px; margin-bottom: 20px; overflow: hidden; border-left: 5px solid #B19446; }
        .narrative .title { background: #f8fafc; padding: 12px 15px; font-weight: 700; color: #0C5B47; border-bottom: 1px solid #E2E8F0; display: flex; align-items: center; gap: 10px; font-size: 11pt; }
        .narrative .body { padding: 15px; line-height: 1.7; font-size: 10.5pt; }
        .meta-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px; }
        .meta-item { text-align: center; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px; }
        .meta-label { font-size: 9pt; color: #64748B; margin-bottom: 5px; }
        .meta-value { font-weight: 700; color: #0C5B47; font-size: 11pt; }
    `;

    const date = new Date().toLocaleDateString('ar-EG');
    
    // Helper to safely get nested properties
    const get = (obj, path, def = '—') => {
        return path.split('.').reduce((o, p) => (o && o[p] !== undefined) ? o[p] : null, obj) || def;
    };

    return `
        <style>${css}</style>
        
        <!-- Cover Page -->
        <div class="page cover-page">
            <div class="cover-header">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px;">
                    <div style="text-align: right;">
                        <strong style="font-size: 12pt;">الجمهورية اليمنية</strong><br>
                        <span style="font-size: 11pt;">اللجنة الزراعية والسمكية العليا</span>
                    </div>
                    <img class="logo" src="${logoUrl}" alt="الشعار" style="max-height: 90px;">
                    <div style="text-align: left;">
                        <strong style="font-size: 12pt;">Yemen Republic</strong><br>
                        <span style="font-size: 11pt;">Higher Agri & Fisheries Com.</span>
                    </div>
                </div>
                <div>
                    <h1>وثيقة مشروع رسمية</h1>
                    <div style="width: 60px; height: 3px; background: #B19446; margin: 10px auto;"></div>
                </div>
            </div>

            <div class="project-identity">
                <span class="doc-label">بطاقة تعريف المشروع</span>
                <h3>${escapeHTML(project.project_name)}</h3>
                <p style="color: #64748B; max-width: 90%; margin: 20px auto; font-size: 12pt;">
                    ${escapeHTML(get(project, 'detail.project_summary', 'خلاصة تنفيذية تصف أهداف المشروع ونطاق عمله'))}
                </p>
                ${project.qrCodeBase64 ? `<div style="margin-top: 25px;"><img src="${project.qrCodeBase64}" style="width: 100px; border: 3px solid #f0f0f0; border-radius: 8px;"><div style="font-size: 9pt; color: #64748B; margin-top: 5px;">كود التحقق الرقمي</div></div>` : ''}
            </div>

            <div class="cover-footer-meta">
                <div style="text-align: center; border-left: 1px solid #E2E8F0; padding-left: 10px;">
                    <div style="font-size: 9pt; color: #64748B;">رقم النموذج</div>
                    <div style="font-weight: 700; font-size: 11pt;">${escapeHTML(project.form_number)}</div>
                </div>
                <div style="text-align: center; border-left: 1px solid #E2E8F0; padding-left: 10px;">
                    <div style="font-size: 9pt; color: #64748B;">تاريخ الإصدار</div>
                    <div style="font-weight: 700; font-size: 11pt;">${date}</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 9pt; color: #64748B;">التكلفة التقديرية</div>
                    <div style="font-weight: 700; color: #0C5B47; font-size: 11pt;">${formatPrice(project.total_cost)} ر.ي</div>
                </div>
            </div>
        </div>

        <!-- Page 2: Basic Info -->
        <div class="page">
            <div class="page-header">
                <span style="font-size: 10pt; color: #0C5B47; font-weight: 700;">${escapeHTML(project.project_name)}</span>
                <span style="font-size: 10pt;">صفحة | 2</span>
            </div>

            <div class="card" style="border-top: 5px solid #0C5B47;">
                <div class="meta-grid">
                    <div class="meta-item">
                        <div class="meta-label">رقم النموذج</div>
                        <div class="meta-value">${escapeHTML(project.form_number)}</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">تاريخ الإصدار</div>
                        <div class="meta-value">${date}</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">حالة الوثيقة</div>
                        <div class="meta-value"><span class="badge b-primary">${escapeHTML(project.status)}</span></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="grid">
                    <div class="item"><div class="label">إجمالي تكلفة المشروع</div><div class="value money">${formatPrice(project.total_cost)} ر.ي</div></div>
                    <div class="item"><div class="label">عدد المواقع</div><div class="value">${project.locations_count || 0}</div></div>
                    <div class="item"><div class="label">عدد الأنشطة التنفيذية</div><div class="value">${project.executive_activities_count || 0}</div></div>
                    <div class="item"><div class="label">عدد المستفيدين</div><div class="value">${escapeHTML(project.number_of_beneficiaries)}</div></div>
                    <div class="item"><div class="label">تاريخ البدء</div><div class="value">${escapeHTML(project.start_date)}</div></div>
                    <div class="item"><div class="label">تاريخ الانتهاء</div><div class="value">${escapeHTML(project.end_date)}</div></div>
                </div>
            </div>

            <h2 class="section-title"><span class="idx">١</span> البيانات الأساسية للمشروع</h2>
            <div class="card">
                <div class="card-head">معلومات المشروع</div>
                <div class="grid">
                    <div class="item"><div class="label">البرنامج</div><div class="value">${escapeHTML(get(project, 'program.name'))}</div></div>
                    <div class="item"><div class="label">المجال</div><div class="value">${escapeHTML(get(project, 'domain.name'))}</div></div>
                    <div class="item"><div class="label">المجال الفرعي</div><div class="value">${escapeHTML(get(project, 'subdomain.name'))}</div></div>
                    <div class="item"><div class="label">نوع التدخل</div><div class="value">${escapeHTML(get(project, 'intervention.name'))}</div></div>
                    <div class="item"><div class="label">الأولوية</div><div class="value">${escapeHTML(get(project, 'priority.name'))}</div></div>
                    <div class="item"><div class="label">المسار الرئيسي</div><div class="value">${escapeHTML(get(project, 'main_router.name'))}</div></div>
                    <div class="item"><div class="label">المسار الفرعي</div><div class="value">${escapeHTML(get(project, 'sub_router.name'))}</div></div>
                    <div class="item"><div class="label">الفئة المستهدفة</div><div class="value">${escapeHTML(get(project, 'target_category.name'))}</div></div>
                </div>
            </div>

            ${project.detail ? `
            <h2 class="section-title"><span class="idx">٢</span> الملخص التنفيذي والمبررات</h2>
            <div class="narrative">
                <div class="title">ملخص المشروع</div>
                <div class="body">${escapeHTML(project.detail.project_summary)}</div>
            </div>
            <div class="narrative">
                <div class="title">المشكلة والمبررات</div>
                <div class="body">${escapeHTML(project.detail.problem_and_justification)}</div>
            </div>
            ` : ''}

            ${project.locations && project.locations.length ? `
            <h2 class="section-title"><span class="idx">٣</span> النطاق الجغرافي</h2>
            <div class="card">
                <div class="card-head">مواقع التنفيذ</div>
                <table class="t">
                    <thead>
                        <tr>
                            <th style="width: 25%;">المحافظة</th>
                            <th style="width: 25%;">المديرية</th>
                            <th style="width: 25%;">المنطقة الفرعية</th>
                            <th style="width: 25%;">القرية / المحلة</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${project.locations.map(loc => `
                            <tr>
                                <td>${escapeHTML(get(loc, 'governorate.name'))}</td>
                                <td>${escapeHTML(get(loc, 'directorate.name'))}</td>
                                <td>${escapeHTML(get(loc, 'sub_area.name'))}</td>
                                <td>${escapeHTML(get(loc, 'village.name'))}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
            ` : ''}

            ${(project.main_objectives?.length || project.special_objectives?.length) ? `
            <h2 class="section-title"><span class="idx">٤</span> الأهداف والنتائج</h2>
            
            ${project.main_objectives?.length ? `
            <div class="card">
                <div class="card-head">الأهداف الرئيسية العامة</div>
                <div class="grid">
                    ${project.main_objectives.map((obj, i) => `
                        <div class="narrative" style="border-left-color: #B19446;">
                            <div class="title" style="border-bottom: none;">الهدف الرئيسي ${i + 1}</div>
                            <div class="body">${escapeHTML(obj.objective)}</div>
                        </div>
                    `).join('')}
                </div>
            </div>
            ` : ''}

            ${project.special_objectives?.length ? `
            <div class="card">
                <div class="card-head">الأهداف الخاصة والمخرجات التفصيلية</div>
                ${project.special_objectives.map(obj => `
                    <div class="narrative" style="margin-bottom:15px; border-left-color: #1E3A8A;">
                        <div class="title">الهدف الخاص: ${escapeHTML(obj.objective)}</div>
                        ${obj.results?.length ? `
                        <div class="body">
                            ${obj.results.map(res => `
                                <div style="margin: 10px 0; padding: 10px; border: 1px solid #E2E8F0; border-radius: 8px; border-left: 4px solid #059669;">
                                    <div style="font-weight: 600; color: #084234; margin-bottom: 8px;">
                                        النتيجة المتوقعة: ${escapeHTML(res.result)}
                                    </div>
                                    ${res.description ? `<div style="padding: 8px 0; font-size: 10pt; color: #1E293B;">${escapeHTML(res.description)}</div>` : ''}
                                </div>
                            `).join('')}
                        </div>
                        ` : ''}
                    </div>
                `).join('')}
            </div>
            ` : ''}
            ` : ''}
        </div>
    `;
}