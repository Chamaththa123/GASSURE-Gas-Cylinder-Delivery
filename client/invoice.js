document.getElementById('downloadInvoice').addEventListener('click', () => {
    const {
        jsPDF
    } = window.jspdf;

    fetch('fetch_latest_order.php', {
            method: 'GET',
        })
        .then(response => response.json())
        .then(orderData => {
            if (!orderData.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: orderData.message || 'Failed to fetch order details.',
                });
                return;
            }

            const {
                order_id,
                user,
                city,
                delivery_fee,
                final_amount,
                order_date
            } = orderData;
            console.log('orderData', orderData)
            const doc = new jsPDF({
                orientation: "portrait",
                unit: "mm",
                format: [200, 200], // Custom width and height in mm
            });

            // Helper function to center text
            const centerText = (text, yPosition, fontSize = 12, font = "helvetica", style =
                "normal") => {
                doc.setFont(font, style);
                doc.setFontSize(fontSize);
                const textWidth = doc.getStringUnitWidth(text) * fontSize / doc.internal
                    .scaleFactor;
                const pageWidth = doc.internal.pageSize.width;
                const xPosition = (pageWidth - textWidth) / 2;
                doc.text(text, xPosition, yPosition);
            };

            // Add title and company details
            centerText("RECEIPT", 20, 18, "helvetica", "bold");
            centerText("GASSURE (PVT) LTD", 27, 12, "helvetica", "semi-bold");
            centerText("Tel : 011-2345534 | Email: contact@gassure.com", 33, 10);
            centerText("123 Gas Avenue, Colombo, Sri Lanka", 39, 10);

            // Use data from orderData
            doc.setFont("helvetica", "bold");
            doc.setFontSize(11);
            // doc.text(`Customer : ${delivery_name}`, 14, 45);
            doc.text(`ORDER :  OR#${order_id}`, 14, 55);
            doc.setFont("helvetica", "normal");
            doc.setFontSize(10);
            doc.text(`Customer :  ${user.first_name} ${user.last_name}`, 14, 65);
            doc.text(`Email :  ${user.email}`, 14, 70);
            const orderDate = new Date(order_date).toISOString().split('T')[
                0]; // Extracts the date portion
            doc.text(`Date :  ${orderDate}`, 14, 75);
            // doc.text(`Address : ${delivery_address}`, 14, 50);
            // doc.text(`Phone : ${contact}`, 14, 55);

            // Gather selected item details
            const selectedItem = document.querySelector('input[name="item_id"]:checked');
            if (!selectedItem) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please select an item to generate the invoice.',
                });
                return;
            }

            // Extract dynamic data
            const itemDescription = selectedItem.dataset.description;
            const itemPrice = parseFloat(selectedItem.dataset.price);
            const quantity = parseInt(document.getElementById('quantity').value);
            const totalPrice = (itemPrice * quantity).toFixed(2);

            // Define table data
            const tableColumns = ["Item Name", "Unit Price", "Quantity", "Total"];
            const tableRows = [
                [itemDescription, `Rs. ${itemPrice.toFixed(2)}`, quantity, `Rs. ${totalPrice}`]
            ];

            // Add table using autoTable plugin
            doc.autoTable({
                head: [tableColumns],
                body: tableRows,
                startY: 80, // Position below the sample text
                theme: "grid",
                headStyles: {
                    fillColor: [187, 187, 187], // Blue header background
                    textColor: [0, 0, 0], // White header text
                    fontStyle: "bold",
                },
                bodyStyles: {
                    textColor: [0, 0, 0], // Black body text
                },
            });

            // Add total amount below the table
            doc.text(`Sub Total: Rs. ${totalPrice}`, 14, doc.lastAutoTable.finalY + 10);
            doc.text(`Delivery Fee : ${city} - Rs ${delivery_fee}`, 14, doc.lastAutoTable.finalY + 15);
            doc.text(`Total Amount : Rs. ${final_amount}`, 14, doc.lastAutoTable.finalY + 20);
            doc.text(`Payment : Paid`, 14, doc.lastAutoTable.finalY + 25);
            doc.setFont("helvetica", "normal");

            doc.setFontSize(9);
            doc.text(
                `- We will notify you of any delays, and you will be updated with tracking information once the order ships.`,
                14, doc.lastAutoTable.finalY + 45);
            doc.text(
                `- Ensure someone is available at the provided address to receive the package. Delivery cannot be rescheduled `,
                14, doc.lastAutoTable.finalY + 50);
            doc.text(`  once dispatched`, 14, doc.lastAutoTable.finalY + 53);
            centerText("============================ Thank You ============================", 175, 14,
                "helvetica", "medium");
            centerText("Glad to see you again !!", 180, 10, "helvetica", "normal");

            // Save the PDF with a custom name
            doc.save('GASSURE-RECEIPT.pdf');
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while fetching order details.',
            });
            console.error('Fetch error:', error);
        });
});